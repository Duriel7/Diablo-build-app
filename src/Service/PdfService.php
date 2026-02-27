<?php

namespace Diablo\Service;

use Mpdf\Mpdf;

class PdfService
{
    private Mpdf $mpdf;

    public function __construct()
    {
        $tempDir = '/var/www/html/www/tmp_mpdf';

        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $this->mpdf = new Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'tempDir' => $tempDir
        ]);
    }

    //Generate PDF content as binary string
    public function generateBinaryPdf(string $html): string
    {
        $this->mpdf->WriteHTML($html);
        return $this->mpdf->Output('', 'S');
    }
}