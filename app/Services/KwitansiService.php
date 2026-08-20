<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class KwitansiService
{
    /**
     * Render kwitansi ke base64 PDF.
     *
     * @param  array<string, mixed>  $payload
     */
    public function pdf(array $payload): string
    {
        $memory = (int) ini_get('memory_limit');

        if ($memory > 0 && $memory < 256) {
            @ini_set('memory_limit', '256M');
        }

        @set_time_limit(120);

        $pdf = Pdf::loadView('pdf.kwitansi', $payload)
            ->setPaper('a5', 'landscape');

        return base64_encode($pdf->output());
    }
}