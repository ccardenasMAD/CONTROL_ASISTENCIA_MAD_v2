<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Asistencia</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: center; }
        .name-column { text-align: left; width: 150px; font-weight: bold; }
        .header { text-align: center; text-transform: uppercase; margin-bottom: 20px; }
        .present { color: green; }
        .absent { color: red; }
        .late { color: orange; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Control de Asistencia - {{ $currentMonthName }}</h2>
    </div>

    <table>
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th class="name-column">Empleado</th>
                @foreach($daysInMonth as $day)
                    <th>{{ $day->format('d') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td class="name-column">{{ $user->name }}</td>
                    @foreach($daysInMonth as $day)
                        @php
                            $attendance = $user->attendances->firstWhere('attendance_date', $day->format('Y-m-d'));
                            $status = $attendance ? substr($attendance->status, 0, 1) : '-';
                        @endphp
                        <td class="{{ $attendance ? $attendance->status : '' }}">
                            {{ strtoupper($status) }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>