<?php

namespace App\Services;

use Illuminate\Http\Response;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfService
{
    /**
     * Render a Blade view into a standardized A4 PDF response streamed to the browser.
     *
     * @param  string  $view  Blade view name
     * @param  array<string, mixed>  $data  View variables
     * @param  string  $filename  Download/tab file name
     * @param  string  $orientation  'P' (Portrait) or 'L' (Landscape)
     */
    public function renderView(
        string $view,
        array $data = [],
        string $filename = 'document.pdf',
        string $orientation = 'P'
    ): Response {
        $html = view($view, $data)->render();

        return $this->generatePdf($html, $filename, $orientation);
    }

    /**
     * Generate an A4 PDF from raw HTML and return as an inline response.
     */
    public function generatePdf(
        string $html,
        string $filename = 'document.pdf',
        string $orientation = 'P'
    ): Response {
        $tempDir = storage_path('app/temp/mpdf');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $orientation,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'tempDir' => $tempDir,
            'autoPageBreak' => true,
        ]);

        $mpdf->SetTitle(pathinfo($filename, PATHINFO_FILENAME));
        $mpdf->WriteHTML($html);

        $pdfOutput = $mpdf->Output('', Destination::STRING_RETURN);

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'private, max-age=0, must-revalidate',
            'Pragma' => 'public',
        ]);
    }
}
