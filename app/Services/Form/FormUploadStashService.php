<?php

namespace App\Services\Form;

use App\Support\Storage\UploadPaths;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class FormUploadStashService
{
    private const SESSION_ROOT = 'form_upload_stash';

    private function stashDisk(): string
    {
        return UploadPaths::privateDisk();
    }

    private function stashDirectoryRoot(): string
    {
        return UploadPaths::formStashRoot();
    }

    public function keyForRequest(Request $request): string
    {
        $route = $request->route()?->getName();

        if (filled($route)) {
            return $route;
        }

        return 'path:'.Str::slug($request->path(), '.');
    }

    /**
     * Conserve les fichiers envoyés (et fusionne avec un stash existant pour ce formulaire).
     */
    public function stashFromRequest(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $key = $this->keyForRequest($request);
        $existing = $this->fields($key, $request);
        $incoming = $this->flattenUploadedFiles($request->allFiles());

        if ($incoming === [] && $existing === []) {
            return;
        }

        $merged = $existing;

        foreach ($incoming as $field => $files) {
            $merged[$field] = $this->persistUploadedList(
                $request,
                $field,
                $files,
                $merged[$field] ?? null
            );
        }

        $request->session()->put($this->sessionPath($key), [
            'fields' => array_filter($merged),
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Réinjecte les fichiers conservés dans la requête si aucun nouveau fichier n’a été choisi.
     */
    public function mergeIntoRequest(Request $request): bool
    {
        if (! $request->isMethod('POST') || ! $request->hasSession()) {
            return false;
        }

        $key = $this->keyForRequest($request);
        $fields = $this->fields($key, $request);

        if ($fields === []) {
            return false;
        }

        $bag = $request->files->all();
        $changed = false;

        foreach ($fields as $field => $meta) {
            if ($this->requestHasFile($request, $field)) {
                continue;
            }

            $uploaded = $this->uploadedFilesFromMeta($meta);

            if ($uploaded === []) {
                continue;
            }

            $name = str_ends_with($field, '[]')
                ? rtrim($field, '[]')
                : $field;

            $bag[$name] = count($uploaded) === 1
                ? $uploaded[0]
                : $uploaded;
            $changed = true;
        }

        if ($changed) {
            $request->files->replace($bag);
        }

        return $changed;
    }

    public function clearForRequest(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $key = $this->keyForRequest($request);
        $this->clearKey($key, $request);
    }

    public function clearKey(string $key, ?Request $request = null): void
    {
        $fields = $this->fields($key, $request);

        foreach ($fields as $meta) {
            $this->deleteMetaFiles($meta);
        }

        $session = $this->resolveSession($request);

        $session?->forget($this->sessionPath($key));
    }

    private function resolveSession(?Request $request = null): ?Session
    {
        if ($request?->hasSession()) {
            return $request->session();
        }

        if (session()->isStarted()) {
            return session();
        }

        return null;
    }

    /**
     * Libellés à afficher dans les champs file (nom original).
     *
     * @return array<string, string>
     */
    public function displayNamesForRequest(Request $request): array
    {
        $key = $this->keyForRequest($request);
        $out = [];

        foreach ($this->fields($key, $request) as $field => $meta) {
            $names = $this->originalNamesFromMeta($meta);

            if ($names === []) {
                continue;
            }

            $plainField = str_replace('[]', '', $field);
            $out[$plainField] = count($names) === 1
                ? $names[0]
                : implode(', ', $names);
        }

        return $out;
    }

    public function hasStashedField(Request $request, string $field): bool
    {
        $key = $this->keyForRequest($request);
        $plain = str_replace('[]', '', $field);

        $fields = $this->fields($key, $request);

        return isset($fields[$plain])
            || isset($fields[$plain.'[]']);
    }

    /**
     * @return array<string, mixed>
     */
    private function fields(string $key, ?Request $request = null): array
    {
        $session = $this->resolveSession($request);

        if (! $session) {
            return [];
        }

        $payload = $session->get($this->sessionPath($key));

        return is_array($payload['fields'] ?? null)
            ? $payload['fields']
            : [];
    }

    private function sessionPath(string $key): string
    {
        return self::SESSION_ROOT.'.'.$key;
    }

    /**
     * @param  array<int, UploadedFile>|UploadedFile  $files
     * @return array<int, array{path: string, original_name: string, mime: string|null, size: int}>
     */
    private function persistUploadedList(
        Request $request,
        string $field,
        array|UploadedFile $files,
        ?array $previousMeta
    ): array {
        if ($previousMeta !== null) {
            $this->deleteMetaFiles($previousMeta);
        }

        $list = $files instanceof UploadedFile ? [$files] : $files;
        $stored = [];
        $sessionId = $request->session()->getId();
        $dir = $this->stashDirectoryRoot().'/'.$sessionId.'/'.Str::slug($field, '_');

        foreach ($list as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store($dir, $this->stashDisk());
            $stored[] = [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $stored;
    }

    /**
     * @return array<string, array<int, UploadedFile>|UploadedFile>
     */
    private function flattenUploadedFiles(array $files, string $prefix = ''): array
    {
        $flat = [];

        foreach ($files as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix.'['.$key.']';

            if ($value instanceof UploadedFile) {
                $flat[$name] = $value;

                continue;
            }

            if (is_array($value)) {
                $allUploaded = Arr::where($value, fn ($v) => $v instanceof UploadedFile);

                if (count($allUploaded) === count($value) && $value !== []) {
                    $plain = str_replace('[]', '', $name);
                    $flat[$plain] = array_values($value);

                    continue;
                }

                $flat = array_merge($flat, $this->flattenUploadedFiles($value, $name));
            }
        }

        return $flat;
    }

    private function requestHasFile(Request $request, string $field): bool
    {
        $plain = str_replace('[]', '', $field);

        return $request->hasFile($plain);
    }

    /**
     * @param  array<int, array<string, mixed>>|array<string, mixed>  $meta
     */
    private function metaIsList(array $meta): bool
    {
        return isset($meta[0]) && is_array($meta[0]);
    }

    /**
     * @param  array<int, array<string, mixed>>|array<string, mixed>  $meta
     * @return array<int, UploadedFile>
     */
    private function uploadedFilesFromMeta(array $meta): array
    {
        $items = $this->metaIsList($meta) ? $meta : [$meta];
        $uploaded = [];

        foreach ($items as $item) {
            $path = $item['path'] ?? null;

            if (! $path) {
                continue;
            }

            $fullPath = Storage::disk($this->stashDisk())->path($path);

            if (! is_readable($fullPath)) {
                continue;
            }
            $uploaded[] = UploadedFile::createFromBase(
                new SymfonyUploadedFile(
                    $fullPath,
                    (string) ($item['original_name'] ?? basename($path)),
                    $item['mime'] ?? null,
                    null,
                    true
                )
            );
        }

        return $uploaded;
    }

    /**
     * @param  array<int, array<string, mixed>>|array<string, mixed>  $meta
     * @return array<int, string>
     */
    private function originalNamesFromMeta(array $meta): array
    {
        $items = $this->metaIsList($meta) ? $meta : [$meta];
        $names = [];

        foreach ($items as $item) {
            if (! empty($item['original_name'])) {
                $names[] = (string) $item['original_name'];
            }
        }

        return $names;
    }

    /**
     * @param  array<int, array<string, mixed>>|array<string, mixed>  $meta
     */
    private function deleteMetaFiles(array $meta): void
    {
        $items = $this->metaIsList($meta) ? $meta : [$meta];

        foreach ($items as $item) {
            $path = $item['path'] ?? null;

            if ($path && Storage::disk($this->stashDisk())->exists($path)) {
                Storage::disk($this->stashDisk())->delete($path);
            }
        }
    }
}
