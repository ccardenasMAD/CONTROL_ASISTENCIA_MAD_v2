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
            'Evento',
            'Hora',
            'Estado',
        ];
    }

    public function map($attendance): array
    {
        return [
            optional($attendance->date)->format('d/m/Y') ?? '—',
            $attendance->user->name ?? '—',
            $attendance->group->name ?? '—',
            match ($attendance->type) {
                'in' => 'Entrada',
                'out' => 'Salida',
                default => '—',
            },
            $attendance->time ? substr($attendance->time, 0, 5) : '—',
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