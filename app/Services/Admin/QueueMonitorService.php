<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class QueueMonitorService
{
    public function driver(): string
    {
        return (string) config('queue.default', 'sync');
    }

    public function supportsDatabaseMonitor(): bool
    {
        return $this->driver() === 'database';
    }

    /**
     * @return Collection<int, object{
     *     id: int,
     *     queue: string,
     *     attempts: int,
     *     reserved_at: ?int,
     *     available_at: int,
     *     created_at: int,
     *     status: string,
     *     job_label: string,
     *     job_class: ?string
     * }>
     */
    public function pendingJobs(int $limit = 100): Collection
    {
        if (! $this->supportsDatabaseMonitor()) {
            return collect();
        }

        return DB::table('jobs')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (object $row): object {
                $parsed = $this->parsePayload((string) $row->payload);

                $reservedAt = $row->reserved_at ? (int) $row->reserved_at : null;
                $status = $reservedAt !== null ? 'processing' : 'pending';

                return (object) [
                    'id' => (int) $row->id,
                    'queue' => (string) $row->queue,
                    'attempts' => (int) $row->attempts,
                    'reserved_at' => $reservedAt,
                    'available_at' => (int) $row->available_at,
                    'created_at' => (int) $row->created_at,
                    'created_at_label' => $this->formatUnixTime((int) $row->created_at) ?? '—',
                    'status' => $status,
                    'job_label' => $parsed['label'],
                    'job_class' => $parsed['class'],
                ];
            });
    }

    /**
     * @return Collection<int, object{
     *     id: int,
     *     uuid: string,
     *     queue: string,
     *     failed_at: string,
     *     job_label: string,
     *     job_class: ?string,
     *     exception_preview: string
     * }>
     */
    public function failedJobs(int $limit = 100): Collection
    {
        if (! $this->supportsDatabaseMonitor()) {
            return collect();
        }

        return DB::table('failed_jobs')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (object $row): object {
                $parsed = $this->parsePayload((string) $row->payload);
                $exception = (string) $row->exception;

                return (object) [
                    'id' => (int) $row->id,
                    'uuid' => (string) $row->uuid,
                    'queue' => (string) $row->queue,
                    'failed_at' => (string) $row->failed_at,
                    'job_label' => $parsed['label'],
                    'job_class' => $parsed['class'],
                    'exception_preview' => Str::limit(trim(Str::before($exception, "\n")), 240),
                ];
            });
    }

    public function pendingCount(): int
    {
        if (! $this->supportsDatabaseMonitor()) {
            return 0;
        }

        return (int) DB::table('jobs')->count();
    }

    public function failedCount(): int
    {
        if (! $this->supportsDatabaseMonitor()) {
            return 0;
        }

        return (int) DB::table('failed_jobs')->count();
    }

    public function formatUnixTime(?int $timestamp): ?string
    {
        if ($timestamp === null || $timestamp <= 0) {
            return null;
        }

        return Carbon::createFromTimestamp($timestamp)
            ->timezone(config('app.timezone'))
            ->format('d/m/Y H:i:s');
    }

    /**
     * @return array{label: string, class: ?string}
     */
    public function parsePayload(string $payload): array
    {
        try {
            $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return ['label' => 'Job (payload illisible)', 'class' => null];
        }

        $displayName = is_string($data['displayName'] ?? null) ? $data['displayName'] : null;
        $class = $displayName;

        if ($displayName !== null) {
            return [
                'label' => class_basename($displayName),
                'class' => $displayName,
            ];
        }

        $commandName = $data['data']['commandName'] ?? null;
        if (is_string($commandName) && $commandName !== '') {
            return [
                'label' => class_basename($commandName),
                'class' => $commandName,
            ];
        }

        return ['label' => 'Job', 'class' => null];
    }
}
