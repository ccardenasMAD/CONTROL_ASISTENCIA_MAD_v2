<div class="text-[16px] leading-normal">

<section class="mt-6">

    {{-- HEADER --}}
    <div class="flex items-end justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold text-white tracking-tight">Productividad General</h2>
            <p class="text-xs text-slate-400 mt-0.5">Vista combinada semanal y mensual</p>
        </div>

        <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/5 border border-white/10 text-[10px] text-slate-300">
            <span class="w-1 h-1 rounded-full bg-emerald-400 animate-pulse"></span>
            En vivo
        </span>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">

        {{-- Promedio semanal --}}
        <div class="relative overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-indigo-500/10 via-white/[0.02] to-transparent p-4">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-indigo-500/20 blur-xl opacity-40"></div>

            <div class="flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-wider text-indigo-300">
                <span class="w-1 h-1 rounded-full bg-indigo-400"></span>
                Promedio semanal
            </div>

            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-2xl font-bold text-white tabular-nums">{{ $weeklyAvg }}</span>
                <span class="text-xs text-slate-400">h/día</span>
            </div>

            <p class="mt-1 text-[10px] text-slate-500">Últimos 7 días</p>
        </div>

        {{-- Promedio mensual --}}
        <div class="relative overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-emerald-500/10 via-white/[0.02] to-transparent p-4">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-emerald-500/20 blur-xl opacity-40"></div>

            <div class="flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-wider text-emerald-300">
                <span class="w-1 h-1 rounded-full bg-emerald-400"></span>
                Promedio mensual
            </div>

            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-2xl font-bold text-white tabular-nums">{{ $monthlyAvg }}</span>
                <span class="text-xs text-slate-400">h/día</span>
            </div>

            <p class="mt-1 text-[10px] text-slate-500">Mes en curso</p>
        </div>

        {{-- Total días --}}
        <div class="relative overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-violet-500/10 via-white/[0.02] to-transparent p-4">
            <div class="absolute -right-4 -top-4 w-12 h-12 rounded-full bg-violet-500/20 blur-xl opacity-40"></div>

            <div class="flex items-center gap-1.5 text-[10px] font-medium uppercase tracking-wider text-violet-300">
                <span class="w-1 h-1 rounded-full bg-violet-400"></span>
                Total días
            </div>

            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-2xl font-bold text-white tabular-nums">{{ count($monthlyData) }}</span>
                <span class="text-xs text-slate-400">días</span>
            </div>

            <p class="mt-1 text-[10px] text-slate-500">Periodo evaluado</p>
        </div>

    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Semanal --}}
        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-white">Productividad semanal</h3>
                <span class="text-[10px] text-slate-500 uppercase tracking-wider">Horas / día</span>
            </div>
            <div class="relative h-56">
                <canvas id="weeklyChart"></canvas>
            </div>
        </div>

        {{-- Mensual --}}
        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-white">Productividad mensual</h3>
                <span class="text-[10px] text-slate-500 uppercase tracking-wider">Horas / día</span>
            </div>
            <div class="relative h-56">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

    </div>

</section>

{{-- VARIABLES PHP → JSON --}}
@php
    $weeklyLabelsJson = json_encode($weeklyLabels);
    $weeklyDataJson = json_encode($weeklyData);

    $monthlyLabelsJson = json_encode($monthlyLabels);
    $monthlyDataJson = json_encode($monthlyData);
@endphp

<script>
document.addEventListener("DOMContentLoaded", () => {

    const weeklyLabels = JSON.parse('{!! $weeklyLabelsJson !!}');
    const weeklyData   = JSON.parse('{!! $weeklyDataJson !!}');

    const monthlyLabels = JSON.parse('{!! $monthlyLabelsJson !!}');
    const monthlyData   = JSON.parse('{!! $monthlyDataJson !!}');

    const textColor = "#cbd5e1";
    const gridColor = "rgba(255,255,255,0.05)";
    const borderColor = "rgba(255,255,255,0.15)";

    // SEMANAL
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: weeklyLabels,
            datasets: [{
                label: 'Horas trabajadas',
                data: weeklyData,
                backgroundColor: ['#6366f1','#22c55e','#eab308','#ef4444'],
                borderRadius: 6,
                borderSkipped: false,
                borderColor: borderColor,
                borderWidth: 1.2,
            }]
        },
        options: {
            responsive: true,
            animation: { duration: 800, easing: 'easeOutQuart' },
            plugins: {
                legend: { labels: { color: textColor } },
                tooltip: {
                    backgroundColor: "#0f172a",
                    borderColor: "#1e293b",
                    borderWidth: 1,
                    titleColor: "#fff",
                    bodyColor: "#cbd5e1",
                }
            },
            scales: {
                x: { ticks: { color: textColor }, grid: { color: gridColor } },
                y: { ticks: { color: textColor }, grid: { color: gridColor }, beginAtZero: true }
            }
        }
    });

    // MENSUAL
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Horas trabajadas',
                data: monthlyData,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139,92,246,0.25)',
                borderWidth: 2,
                tension: 0.35,
                fill: true,
                pointRadius: 2.5,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#fff',
                pointBorderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            animation: { duration: 900, easing: 'easeOutQuart' },
            plugins: {
                legend: { labels: { color: textColor } },
                tooltip: {
                    backgroundColor: "#0f172a",
                    borderColor: "#1e293b",
                    borderWidth: 1,
                    titleColor: "#fff",
                    bodyColor: "#cbd5e1",
                }
            },
            scales: {
                x: { ticks: { color: textColor }, grid: { color: gridColor } },
                y: { ticks: { color: textColor }, grid: { color: gridColor }, beginAtZero: true }
            }
        }
    });

});
</script>

</div>
