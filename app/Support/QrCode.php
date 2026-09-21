<?php

namespace App\Support;

require_once dirname(__DIR__, 2) . '/vendor/qrcode/qrcode.php';

/**
 * Thin wrapper around the vendored (dependency-free) kazuhikoarase QR
 * generator, rendering to a GD image. Used for certificate verification
 * QR codes.
 */
class QrCode
{
    /**
     * @return string PNG binary data
     */
    public static function png(string $data, int $pixelsPerModule = 6, int $margin = 2): string
    {
        $qr = \QRCode::getMinimumQRCode($data, QR_ERROR_CORRECT_LEVEL_M);
        $moduleCount = $qr->getModuleCount();
        $size = ($moduleCount + $margin * 2) * $pixelsPerModule;

        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $size, $size, $white);

        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if ($qr->isDark($row, $col)) {
                    $x = ($col + $margin) * $pixelsPerModule;
                    $y = ($row + $margin) * $pixelsPerModule;
                    imagefilledrectangle($image, $x, $y, $x + $pixelsPerModule - 1, $y + $pixelsPerModule - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return $binary;
    }

    public static function dataUri(string $data, int $pixelsPerModule = 6, int $margin = 2): string
    {
        return 'data:image/png;base64,' . base64_encode(self::png($data, $pixelsPerModule, $margin));
    }

    public static function saveTo(string $absolutePath, string $data, int $pixelsPerModule = 6, int $margin = 2): void
    {
        file_put_contents($absolutePath, self::png($data, $pixelsPerModule, $margin));
    }
}
