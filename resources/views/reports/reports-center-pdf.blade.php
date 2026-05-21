@php
    function formatStatus($status) {
        return match ($status) {
            'present'     => 'Presente',
            'late'        => 'Atraso',
            'early_exit'  => 'Salida anticipada',
            'incomplete'  => 'Incompleta',
            'absent'      => 'Ausente',
            default       => '—',
        };
    }
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h1 {
            text-align: center;
            margin-bottom: 10px;
        }

        .meta {
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            font-size: 11px;
        }

        th {
            background-color: #f2f2f2;
            text-transform: uppercase;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

<h1>Reporte de Asistencia</h1>

<div class="meta">
    <strong>Período:</strong> {{ $start }} → {{ $end }} <br>
    <strong>Usuario:</strong> {{ $user ?? 'Todos' }} <br>
    <strong>Grupo:</strong> {{ $group ?? 'Todos' }} <br>
    <strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}
</div>

<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Grupo</th>
            <th>Evento</th>
            <th>Hora</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $attendance)
            <tr>
                <td>{{ $attendance->date->format('d/m/Y') }}</td>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->group->name }}</td>
                <td>{{ $attendance->type === 'in' ? 'Entrada' : ($attendance->type === 'out' ? 'Salida' : '—') }}</td>
                <td>{{ $attendance->time ? substr($attendance->time, 0, 5) : '—' }}</td>
                <td>{{ formatStatus($attendance->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>