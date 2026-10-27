<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfReportService
{
    /**
     * @param  array<string, mixed>  $analytics
     * @param  array<string, mixed>  $alerts
     */
    public function render(array $analytics, array $alerts): string
    {
        $logoPath = public_path('images/conversation-room/boston-english-center-logo.png');
        $logoData = is_file($logoPath)
            ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($logoPath))
            : null;
        $html = view('admin.reports.analytics', [
            'analytics' => $analytics,
            'alerts' => $alerts,
            'logoData' => $logoData,
        ])->render();
        $options = new Options;
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->setChroot([public_path(), storage_path('app')]);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
        $dompdf->getCanvas()->page_text(
            500,
            810,
            'Page {PAGE_NUM} of {PAGE_COUNT}',
            $dompdf->getFontMetrics()->getFont('DejaVu Sans'),
            8,
            [0.35, 0.42, 0.55],
        );

        return $dompdf->output();
    }
}
