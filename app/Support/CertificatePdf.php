<?php

namespace App\Support;

use App\Core\Env;

require_once dirname(__DIR__, 2) . '/vendor/fpdf/fpdf.php';

/**
 * Renders a certificate PDF (landscape A4) with the CPI crest, crimson/gold
 * accents, and a QR code linking to the public verification page.
 */
class CertificatePdf
{
    public static function generate(array $certificate, string $fullName, string $courseTitle, string $intakeCode): string
    {
        $pdf = new \FPDF('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->SetMargins(0, 0, 0);

        $w = 297;
        $h = 210;

        // Border
        $pdf->SetDrawColor(122, 16, 16); // crimson
        $pdf->SetLineWidth(2);
        $pdf->Rect(8, 8, $w - 16, $h - 16);
        $pdf->SetDrawColor(201, 162, 39); // gold
        $pdf->SetLineWidth(0.6);
        $pdf->Rect(12, 12, $w - 24, $h - 24);

        // Crest
        $logo = dirname(__DIR__, 2) . '/public/assets/img/logo.png';
        if (is_file($logo)) {
            $pdf->Image($logo, $w / 2 - 15, 18, 30, 30);
        }

        $pdf->SetY(52);
        $pdf->SetFont('Times', 'B', 22);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->Cell(0, 10, 'CRAWFORD PROFESSIONALS INSTITUTE (CPI)', 0, 1, 'C');

        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(107, 100, 90);
        $pdf->Cell(0, 7, 'Empowering Skills, Transforming Lives.', 0, 1, 'C');

        $pdf->Ln(6);
        $pdf->SetFont('Helvetica', '', 13);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->Cell(0, 8, 'This certificate is proudly presented to', 0, 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('Times', 'B', 26);
        $pdf->SetTextColor(122, 16, 16);
        $pdf->Cell(0, 14, $fullName, 0, 1, 'C');

        $pdf->Ln(2);
        $pdf->SetFont('Helvetica', '', 13);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->Cell(0, 8, 'for successfully completing', 0, 1, 'C');

        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->Cell(0, 10, $certificate['title'] ?: $courseTitle, 0, 1, 'C');

        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(107, 100, 90);
        $pdf->Cell(0, 7, $courseTitle . ' (' . $intakeCode . ')', 0, 1, 'C');

        // Footer row: issue date, signature line, QR code
        $footerY = $h - 42;
        $pdf->SetY($footerY);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(20, 20, 20);

        $pdf->SetXY(30, $footerY);
        $pdf->Cell(80, 6, 'Date Issued: ' . date('d F Y', strtotime($certificate['issued_at'])), 0, 2, 'L');
        $pdf->SetXY(30, $footerY + 14);
        $pdf->Cell(80, 0.2, '', 'T');
        $pdf->SetXY(30, $footerY + 16);
        $pdf->Cell(80, 6, 'Registrar, CPI', 0, 2, 'L');

        $pdf->SetXY(187, $footerY);
        $pdf->Cell(80, 6, 'Certificate No: ' . $certificate['code'], 0, 2, 'R');
        $pdf->SetXY(187, $footerY + 14);
        $pdf->Cell(80, 0.2, '', 'T');
        $pdf->SetXY(187, $footerY + 16);
        $pdf->Cell(80, 6, 'Director, CPI', 0, 2, 'R');

        // QR code, center bottom
        $verifyUrl = rtrim((string) Env::get('APP_URL', ''), '/') . '/verify/' . $certificate['code'];
        $qrTmp = sys_get_temp_dir() . '/cpi_qr_' . $certificate['code'] . '.png';
        QrCode::saveTo($qrTmp, $verifyUrl, 4, 1);
        $pdf->Image($qrTmp, $w / 2 - 12, $footerY, 24, 24);
        @unlink($qrTmp);
        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetXY($w / 2 - 20, $footerY + 25);
        $pdf->Cell(40, 4, 'Scan to verify', 0, 0, 'C');

        return $pdf->Output('S'); // return as string
    }

    public static function saveTo(string $absolutePath, array $certificate, string $fullName, string $courseTitle, string $intakeCode): void
    {
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($absolutePath, self::generate($certificate, $fullName, $courseTitle, $intakeCode));
    }
}
