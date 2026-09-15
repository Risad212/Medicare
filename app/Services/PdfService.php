<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    /**
     * Render a blade view to PDF and stream it to the browser.
     */
    public function stream(string $view, array $data, string $filename)
    {
        return Pdf::loadView($view, $data)->stream($filename);
    }

    /**
     * Render a blade view to PDF and force a download.
     */
    public function download(string $view, array $data, string $filename)
    {
        return Pdf::loadView($view, $data)->download($filename);
    }
}
