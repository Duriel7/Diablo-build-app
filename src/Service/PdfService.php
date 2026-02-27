<?php

namespace Diablo\Service;

use Mpdf\Mpdf;

class PdfService
{
    private Mpdf $mpdf;

    public function __construct()
    {
        //Base configuration
        $this->mpdf = new Mpdf([
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);
    }

    //Generate a PDF from HTML and return it as a binary string
    public function generateBinaryPdf(string $html): string
    {
        $this->mpdf->WriteHTML($html);
        return $this->mpdf->Output('', 'S');
    }
}