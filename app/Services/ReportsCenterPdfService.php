<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class ReportsCenterPdfService
{
    public function generate(Collection $records, array $meta)
    {
        return Pdf::loadView('reports.reports-center-pdf', [
            'records' => $records,
            ...$meta,
        ]);
    }
}