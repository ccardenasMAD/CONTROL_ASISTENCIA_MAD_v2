<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportsCenterExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected Collection $records
    ) {}

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Usuario',
            'Grupo',
            'Entrada',
            'Salida',
            'Estado',
        ];
    }

    public function map($attendance): array
    {
        return [
            optional($attendance->attendance_date)->format('d/m/Y') ?? '—',
            $attendance->user->name ?? '—',
            $attendance->group->name ?? '—',
            optional($attendance->check_in)->format('H:i') ?? '—',
            optional($attendance->check_out)->format('H:i') ?? '—',
            $this->formatStatus($attendance->status),
        ];
    }

    // ✅ FIX PRINCIPAL (acepta null)
    private function formatStatus(?string $status): string
    {
        return match ($status) {
            'present'     => 'Presente',
            'late'        => 'Atraso',
            'early_exit'  => 'Salida anticipada',
            'incomplete'  => 'Incompleta',
            'absent'      => 'Ausente',
            default       => '—',
        };
    }
}