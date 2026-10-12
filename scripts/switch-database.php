<?php

/**
 * Switch local .env database block between MySQL and SQLite.
 *
 * Usage: php scripts/switch-database.php mysql|sqlite
 */

$driver = $argv[1] ?? null;

if (! in_array($driver, ['mysql', 'sqlite'], true)) {
    fwrite(STDERR, "Usage: php scripts/switch-database.php mysql|sqlite\n");
    exit(1);
}

$root = dirname(__DIR__);
$envFile = $root.DIRECTORY_SEPARATOR.'.env';
$snippetFile = $root.DIRECTORY_SEPARATOR."env/database.{$driver}";

if (! is_file($envFile)) {
    fwrite(STDERR, "Missing .env at {$envFile}\n");
    exit(1);
}

if (! is_file($snippetFile)) {
    fwrite(STDERR, "Missing snippet at {$snippetFile}\n");
    exit(1);
}

$snippet = trim(file_get_contents($snippetFile));
$snippet = str_replace('__PROJECT_ROOT__', str_replace('\\', '/', $root), $snippet);

$content = file_get_contents($envFile);
$pattern = '/# --- DATABASE ---.*?# --- END DATABASE ---/s';

if (! preg_match($pattern, $content)) {
    fwrite(STDERR, "Could not find # --- DATABASE --- ... # --- END DATABASE --- markers in .env\n");
    fwrite(STDERR, "Copy the database block from .env.example or run: php scripts/switch-database.php {$driver} after adding markers.\n");
    exit(1);
}

$replacement = "# --- DATABASE ---\n"
    ."# Active driver: {$driver} (switch: composer db:mysql | composer db:sqlite)\n"
    .$snippet."\n"
    .'# --- END DATABASE ---';

$content = preg_replace($pattern, $replacement, $content, 1);
file_put_contents($envFile, $content);

if ($driver === 'sqlite') {
    $dbPath = $root.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'database.sqlite';
    if (! is_file($dbPath)) {
        touch($dbPath);
        echo "Created empty SQLite file: {$dbPath}\n";
    }
}

passthru('php artisan config:clear', $exitCode);

echo "Database switched to {$driver}.\n";

if ($driver === 'mysql') {
    echo "Ensure MySQL is running: docker compose up -d\n";
}

exit($exitCode);
