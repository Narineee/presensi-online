<?php

namespace App\Services;

use Illuminate\Http\Response;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
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
     * @param  array<string, mixed>  $options  Custom mPDF options override
     */
    public function renderView(
        string $view,
        array $data = [],
        string $filename = 'document.pdf',
        string $orientation = 'P',
        array $options = []
    ): Response {
        $data['isPdf'] = true;

        if (! isset($data['logoBase64'])) {
            $logoPath = public_path('storage/images/logo-kalsel.png');
            if (! file_exists($logoPath)) {
                $logoPath = storage_path('app/public/images/logo-kalsel.png');
            }
            $data['logoBase64'] = file_exists($logoPath)
                ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
                : null;
        }

        $html = view($view, $data)->render();

        return $this->generatePdf($html, $filename, $orientation, $options);
    }

    /**
     * Generate an A4 PDF from raw HTML and return as an inline response.
     *
     * @param  array<string, mixed>  $options  Custom mPDF options override
     */
    public function generatePdf(
        string $html,
        string $filename = 'document.pdf',
        string $orientation = 'P',
        array $options = []
    ): Response {
        $tempDir = storage_path('app/temp/mpdf');
        if (! file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $defaultConfigVariables = (new ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfigVariables['fontDir'];

        $defaultFontConfig = (new FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $customFontsDir = public_path('fonts');
        if (file_exists($customFontsDir)) {
            $fontDirs[] = $customFontsDir;
            if (file_exists($customFontsDir.'/GreatVibes-Regular.ttf')) {
                $fontData['greatvibes'] = [
                    'R' => 'GreatVibes-Regular.ttf',
                ];
            }
        }

        $defaultConfig = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => $orientation,
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'tempDir' => $tempDir,
            'autoPageBreak' => true,
            'fontDir' => $fontDirs,
            'fontdata' => $fontData,
        ];

        $config = array_merge($defaultConfig, $options);

        $mpdf = new Mpdf($config);

        if (isset($options['autoPageBreak']) && $options['autoPageBreak'] === false) {
            $mpdf->autoPageBreak = false;
        }

        $mpdf->SetTitle(pathinfo($filename, PATHINFO_FILENAME));
        $mpdf->WriteHTML($html);

        $pdfOutput = $mpdf->Output('', Destination::STRING_RETURN);

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ]);
    }
}
