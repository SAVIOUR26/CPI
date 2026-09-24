<?php

namespace App\Support;

/** Lecture videos are hosted on YouTube, Vimeo or Google Drive and played inside the class page. */
final class Video
{
    /** Embed address for a YouTube, Vimeo or Google Drive link; null for anything else. */
    public static function embedUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if (!self::isWebUrl($url)) {
            return null;
        }
        if (preg_match('~(?:youtube(?:-nocookie)?\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0';
        }
        if (preg_match('~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d+)(?:/([0-9a-f]+))?~', $url, $m)) {
            $hash = $m[2] ?? '';
            if ($hash === '') {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $hash = is_string($query['h'] ?? null) && preg_match('/^[0-9a-f]+$/', $query['h']) ? $query['h'] : '';
            }
            return 'https://player.vimeo.com/video/' . $m[1] . ($hash !== '' ? '?h=' . $hash : '');
        }
        if (preg_match('~drive\.google\.com/(?:file/d/|open\?id=)([A-Za-z0-9_-]{20,})~', $url, $m)) {
            return 'https://drive.google.com/file/d/' . $m[1] . '/preview';
        }
        return null;
    }

    /** Only http(s) links are ever used as a link or embed. */
    public static function isWebUrl(?string $url): bool
    {
        $url = trim((string) $url);
        return (bool) preg_match('~^https?://~i', $url) && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
