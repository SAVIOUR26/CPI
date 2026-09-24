<?php

namespace App\Support;

use App\Controllers\Academic\GatewayController;
use App\Core\Upload;

/**
 * Files uploaded with an academic application. Shared by the admin review
 * screen and the student's My Admission page so both resolve and serve files
 * the same, safe way.
 */
final class ApplicationDocuments
{
    /** Every uploaded file on an application: key, label, url, ext. $base is the URL prefix the key is appended to. */
    public static function list(array $app, string $base): array
    {
        $form = self::form($app);
        $stored = json_decode((string) $app['documents'], true) ?: [];
        $list = [];
        foreach (GatewayController::DOCUMENTS as $key => [$label]) {
            $paths = isset($stored[$key]) ? (array) $stored[$key] : [];
            foreach (array_values($paths) as $i => $path) {
                $list[] = [
                    'key' => $key,
                    'label' => $label . (count($paths) > 1 ? ' (' . ($i + 1) . ')' : ''),
                    'url' => $base . $key . ($i ? '?i=' . $i : ''),
                    'ext' => strtoupper(pathinfo((string) $path, PATHINFO_EXTENSION)),
                ];
            }
        }
        foreach ($form['qualifications'] ?? [] as $i => $q) {
            if (!empty($q['document'])) {
                $list[] = [
                    'key' => 'qualification',
                    'label' => trim(($q['qualification'] ?: 'Qualification') . ($q['institution'] ? ' — ' . $q['institution'] : '')),
                    'url' => $base . 'qualification?i=' . $i,
                    'ext' => strtoupper(pathinfo((string) $q['document'], PATHINFO_EXTENSION)),
                ];
            }
        }
        if (!empty($app['documents_path'])) {
            $list[] = ['key' => 'legacy', 'label' => 'Submitted documents', 'url' => $base . 'legacy',
                       'ext' => strtoupper(pathinfo((string) $app['documents_path'], PATHINFO_EXTENSION))];
        }
        return $list;
    }

    /** The stored file for a document key and index, or null if there is none. */
    public static function path(array $app, string $key, int $index): ?string
    {
        if ($key === 'legacy') {
            $path = $app['documents_path'] ?? null;
            return is_string($path) && $path !== '' && !str_contains($path, '..') && Upload::exists($path) ? $path : null;
        }
        if ($key === 'qualification') {
            $path = self::form($app)['qualifications'][$index]['document'] ?? null;
        } elseif (isset(GatewayController::DOCUMENTS[$key])) {
            $entry = (json_decode((string) $app['documents'], true) ?: [])[$key] ?? null;
            $path = is_array($entry) ? ($entry[$index] ?? null) : ($index === 0 ? $entry : null);
        } else {
            return null;
        }
        // Paths are written by GatewayController; check the shape anyway before touching the filesystem.
        return is_string($path) && preg_match('#^academic-applications/[a-f0-9]{32}\.(pdf|jpe?g|png)$#', $path) && Upload::exists($path) ? $path : null;
    }

    /** A readable file name, e.g. CPI-APP-2026-00012-national-id. */
    public static function downloadName(array $app, string $key, int $index): string
    {
        return ($app['application_no'] ?: 'application-' . $app['id']) . '-' . str_replace('_', '-', $key) . ($index ? '-' . ($index + 1) : '');
    }

    /** Streams the file inline and ends the request. */
    public static function send(string $path, string $downloadName): void
    {
        $file = Upload::absolutePath($path);
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        header('Content-Type: ' . Upload::mimeFor($path));
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: inline; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '-', $downloadName) . '.' . $ext . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($file);
        exit;
    }

    private static function form(array $app): array
    {
        return json_decode((string) ($app['form_data'] ?? ''), true) ?: [];
    }
}
