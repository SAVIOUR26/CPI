<?php

namespace App\Core;

/**
 * Applies database/migrations/NNN_*.sql in order, automatically, on the first
 * request after a deploy. The site is deployed over FTP with no shell access,
 * so there is no other reliable moment to run schema changes.
 *
 * Each statement should be safe to re-run: "duplicate column/key" errors are
 * treated as already-applied, so a migration someone also ran by hand in
 * phpMyAdmin doesn't block the site.
 */
class Migrator
{
    private const TOLERATED_ERRORS = [1060, 1061]; // duplicate column name, duplicate key name
    private const RETRY_AFTER_FAILURE = 600;       // seconds

    public static function run(): void
    {
        $files = glob(BASE_PATH . '/database/migrations/*.sql') ?: [];
        if (!$files) {
            return;
        }
        sort($files);
        $latest = (int) basename(end($files));

        $marker = BASE_PATH . '/storage/cache/schema_version';
        if (is_file($marker) && (int) file_get_contents($marker) >= $latest) {
            return;
        }
        $failMarker = BASE_PATH . '/storage/cache/schema_failed';
        if (is_file($failMarker) && time() - (int) filemtime($failMarker) < self::RETRY_AFTER_FAILURE) {
            return;
        }

        try {
            $pdo = Database::connection();
            $pdo->query("SELECT GET_LOCK('cpi_migrations', 15)")->fetchColumn();
            try {
                $current = (int) ($pdo->query("SELECT `value` FROM settings WHERE `key` = 'schema_version'")->fetchColumn() ?: 0);
                foreach ($files as $file) {
                    $version = (int) basename($file);
                    if ($version <= $current) {
                        continue;
                    }
                    foreach (self::statements((string) file_get_contents($file)) as $sql) {
                        try {
                            $pdo->exec($sql);
                        } catch (\PDOException $e) {
                            if (!in_array((int) ($e->errorInfo[1] ?? 0), self::TOLERATED_ERRORS, true)) {
                                throw new \RuntimeException(basename($file) . ': ' . $e->getMessage(), 0, $e);
                            }
                        }
                    }
                    $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES ('schema_version', ?)
                                   ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)")->execute([(string) $version]);
                    $current = $version;
                }
            } finally {
                $pdo->query("SELECT RELEASE_LOCK('cpi_migrations')")->fetchColumn();
            }
            @file_put_contents($marker, (string) $current);
            if (is_file($failMarker)) {
                @unlink($failMarker);
            }
        } catch (\Throwable $e) {
            error_log('Database migration failed: ' . $e->getMessage());
            @touch($failMarker);
        }
    }

    /** @return string[] */
    private static function statements(string $sql): array
    {
        $sql = (string) preg_replace('/^\s*--.*$/m', '', $sql);
        $parts = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];
        return array_values(array_filter(array_map('trim', $parts), fn ($s) => $s !== ''));
    }
}
