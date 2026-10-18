<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ActiveSessionService
{
    /**
     * @return list<string>
     */
    public function supportedDrivers(): array
    {
        return ['database', 'file'];
    }

    public function driver(): string
    {
        return (string) Config::get('session.driver', 'file');
    }

    public function isManageable(): bool
    {
        return in_array($this->driver(), $this->supportedDrivers(), true);
    }

    public function lifetimeMinutes(): int
    {
        return max(1, (int) Config::get('session.lifetime', 120));
    }

    public function minimumLastActivity(): int
    {
        return time() - ($this->lifetimeMinutes() * 60);
    }

    public function loginSessionKey(): string
    {
        return 'login_web_'.sha1(SessionGuard::class);
    }

    /**
     * @return Collection<int, array{
     *     id: string,
     *     user_id: int|null,
     *     ip_address: string|null,
     *     user_agent: string|null,
     *     last_activity: int,
     *     is_guest: bool,
     *     user: User|null
     * }>
     */
    public function list(?string $search = null, ?int $userId = null, bool $authenticatedOnly = true): Collection
    {
        if (! $this->isManageable()) {
            return collect();
        }

        $sessions = match ($this->driver()) {
            'database' => $this->listFromDatabase(),
            'file' => $this->listFromFiles(),
            default => collect(),
        };

        if ($authenticatedOnly) {
            $sessions = $sessions->filter(fn (array $session) => ! $session['is_guest']);
        }

        if ($userId !== null) {
            $sessions = $sessions->where('user_id', $userId);
        }

        if ($search !== null && $search !== '') {
            $needle = Str::lower($search);
            $sessions = $sessions->filter(function (array $session) use ($needle) {
                $user = $session['user'];
                if ($user === null) {
                    return Str::contains(Str::lower((string) $session['ip_address']), $needle)
                        || Str::contains(Str::lower((string) $session['id']), $needle);
                }

                return Str::contains(Str::lower($user->email ?? ''), $needle)
                    || Str::contains(Str::lower($user->name ?? ''), $needle)
                    || Str::contains(Str::lower($user->username ?? ''), $needle);
            });
        }

        return $sessions
            ->sortByDesc('last_activity')
            ->values();
    }

    public function destroy(string $sessionId): bool
    {
        if (! $this->isValidSessionId($sessionId)) {
            return false;
        }

        return match ($this->driver()) {
            'database' => DB::table('sessions')->where('id', $sessionId)->delete() > 0,
            'file' => File::delete($this->sessionFilePath($sessionId)),
            default => false,
        };
    }

    /**
     * @return array<int, int>
     */
    public function authenticatedCountsByUserId(): array
    {
        if (! $this->isManageable()) {
            return [];
        }

        return $this->list(authenticatedOnly: true)
            ->pluck('user_id')
            ->filter()
            ->countBy()
            ->all();
    }

    public function countForUser(int $userId): int
    {
        return $this->list(userId: $userId, authenticatedOnly: true)->count();
    }

    public function destroyForUser(int $userId, ?string $exceptSessionId = null): int
    {
        $sessions = $this->list(userId: $userId, authenticatedOnly: true);
        $removed = 0;

        foreach ($sessions as $session) {
            if ($exceptSessionId !== null && $session['id'] === $exceptSessionId) {
                continue;
            }

            if ($this->destroy($session['id'])) {
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * @return Collection<int, array{
     *     id: string,
     *     user_id: int|null,
     *     ip_address: string|null,
     *     user_agent: string|null,
     *     last_activity: int,
     *     is_guest: bool,
     *     user: User|null
     * }>
     */
    protected function listFromDatabase(): Collection
    {
        if (! $this->sessionsTableExists()) {
            return collect();
        }

        $rows = DB::table('sessions')
            ->where('last_activity', '>=', $this->minimumLastActivity())
            ->orderByDesc('last_activity')
            ->get();

        $userIds = $rows->pluck('user_id')->filter()->unique()->values();
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($users) {
            $payloadUserId = $this->userIdFromEncodedPayload($row->payload);
            $userId = $row->user_id ?? $payloadUserId;
            $userId = $userId !== null ? (int) $userId : null;

            return $this->normalizeSessionRow(
                id: (string) $row->id,
                userId: $userId,
                ipAddress: $row->ip_address,
                userAgent: $row->user_agent,
                lastActivity: (int) $row->last_activity,
                users: $users,
            );
        });
    }

    /**
     * @return Collection<int, array{
     *     id: string,
     *     user_id: int|null,
     *     ip_address: string|null,
     *     user_agent: string|null,
     *     last_activity: int,
     *     is_guest: bool,
     *     user: User|null
     * }>
     */
    protected function listFromFiles(): Collection
    {
        $directory = (string) Config::get('session.files', storage_path('framework/sessions'));

        if (! is_dir($directory)) {
            return collect();
        }

        $sessions = collect();
        $userIds = collect();

        foreach (File::files($directory) as $file) {
            $sessionId = $file->getFilename();
            if (! $this->isValidSessionId($sessionId)) {
                continue;
            }

            $raw = @file_get_contents($file->getPathname());
            if ($raw === false || $raw === '') {
                continue;
            }

            $attributes = $this->decodeFileSessionPayload($raw);
            if ($attributes === null) {
                continue;
            }

            $lastActivity = (int) ($attributes['last_activity'] ?? $file->getMTime());
            if ($lastActivity < $this->minimumLastActivity()) {
                continue;
            }

            $userId = $this->userIdFromSessionAttributes($attributes);
            if ($userId !== null) {
                $userIds->push($userId);
            }

            $sessions->push([
                'id' => $sessionId,
                'user_id' => $userId,
                'ip_address' => $attributes['_ip_address'] ?? null,
                'user_agent' => $attributes['_user_agent'] ?? null,
                'last_activity' => $lastActivity,
                'is_guest' => $userId === null,
                'user' => null,
            ]);
        }

        $users = User::query()->whereIn('id', $userIds->unique())->get()->keyBy('id');

        return $sessions->map(function (array $session) use ($users) {
            $session['user'] = $session['user_id'] !== null
                ? $users->get($session['user_id'])
                : null;

            return $session;
        });
    }

    /**
     * @param  Collection<int|string, User>  $users
     * @return array{
     *     id: string,
     *     user_id: int|null,
     *     ip_address: string|null,
     *     user_agent: string|null,
     *     last_activity: int,
     *     is_guest: bool,
     *     user: User|null
     * }
     */
    protected function normalizeSessionRow(
        string $id,
        ?int $userId,
        ?string $ipAddress,
        ?string $userAgent,
        int $lastActivity,
        Collection $users,
    ): array {
        return [
            'id' => $id,
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'last_activity' => $lastActivity,
            'is_guest' => $userId === null,
            'user' => $userId !== null ? $users->get($userId) : null,
        ];
    }

    protected function sessionsTableExists(): bool
    {
        return Schema::hasTable('sessions');
    }

    protected function sessionFilePath(string $sessionId): string
    {
        $directory = (string) Config::get('session.files', storage_path('framework/sessions'));

        return $directory.DIRECTORY_SEPARATOR.$sessionId;
    }

    protected function isValidSessionId(string $sessionId): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9,-]{20,128}$/', $sessionId);
    }

    protected function userIdFromEncodedPayload(string $payload): ?int
    {
        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return null;
        }

        return $this->userIdFromSerializedPayload($decoded);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function userIdFromSessionAttributes(array $attributes): ?int
    {
        $loginKey = $this->loginSessionKey();
        if (! array_key_exists($loginKey, $attributes)) {
            return null;
        }

        $userId = $attributes[$loginKey];
        if (! is_numeric($userId)) {
            return null;
        }

        return (int) $userId;
    }

    protected function userIdFromSerializedPayload(string $payload): ?int
    {
        $attributes = @unserialize($payload);
        if (! is_array($attributes)) {
            return null;
        }

        return $this->userIdFromSessionAttributes($attributes);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decodeFileSessionPayload(string $raw): ?array
    {
        try {
            $decoded = Crypt::decrypt($raw, false);
        } catch (DecryptException) {
            $decoded = $raw;
        }

        if (! is_string($decoded)) {
            return null;
        }

        $attributes = @unserialize($decoded);
        if (! is_array($attributes)) {
            return null;
        }

        return $attributes;
    }
}
