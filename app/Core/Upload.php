<?php

namespace App\Core;

class Upload
{
    private const ALLOWED = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'zip' => 'application/zip',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    public static function storagePath(): string
    {
        return dirname(__DIR__, 2) . '/storage/uploads/';
    }

    /**
     * Move an uploaded file into storage/uploads/{subdir}/, with a random-ish
     * filename to avoid collisions and to keep the file unguessable.
     *
     * @return string relative path, stored in DB, e.g. "proofs/6f3.../receipt.pdf"
     * @throws \RuntimeException on validation or move failure
     */
    public static function store(array $file, string $subdir, int $maxBytes = 10 * 1024 * 1024): string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload failed (error code ' . $file['error'] . ').');
        }
        if ($file['size'] > $maxBytes) {
            throw new \RuntimeException('File is too large. Maximum size is ' . round($maxBytes / 1024 / 1024, 1) . 'MB.');
        }

        $originalName = $file['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED[$ext])) {
            throw new \RuntimeException('File type not allowed: .' . $ext);
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid upload.');
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir = self::storagePath() . trim($subdir, '/') . '/';
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Could not create upload directory.');
        }

        $destination = $dir . $safeName;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Could not save uploaded file.');
        }

        return trim($subdir, '/') . '/' . $safeName;
    }

    /** The largest single upload the server will take, capped at $cap (upload_max_filesize and post_max_size allowing). */
    public static function maxBytes(int $cap): int
    {
        $limits = [$cap];
        $file = self::iniBytes((string) ini_get('upload_max_filesize'));
        $post = self::iniBytes((string) ini_get('post_max_size'));
        if ($file > 0) {
            $limits[] = $file;
        }
        if ($post > 0) {
            $limits[] = max(1, $post - 65536); // room for the other form fields
        }
        return min($limits);
    }

    /** "2M" → 2097152 */
    public static function iniBytes(string $value): int
    {
        $value = trim($value);
        $n = (int) $value;
        return match (strtolower(substr($value, -1))) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }

    /** 2097152 → "2MB", 1572864 → "1.5MB" */
    public static function humanSize(int $bytes): string
    {
        return rtrim(rtrim(number_format($bytes / 1048576, 1), '0'), '.') . 'MB';
    }

    public static function absolutePath(string $relativePath): string
    {
        return self::storagePath() . ltrim($relativePath, '/');
    }

    public static function exists(string $relativePath): bool
    {
        return is_file(self::absolutePath($relativePath));
    }

    public static function mimeFor(string $relativePath): string
    {
        $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
        return self::ALLOWED[$ext] ?? 'application/octet-stream';
    }
}
