
    {{-- CONTENEDOR IDÉNTICO A TU TARJETA DE ARRIBA --}}
    <div class="p-6 rounded-2xl bg-slate-900/70 backdrop-blur ring-1 ring-slate-700/60 shadow-xl space-y-6">

        {{-- HEADER CON TU ESTILO DE DEGRADADOS Y MEDIDAS --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Productividad General</h1>
                </div>
                
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-900/70 ring-1 ring-slate-700/60 text-[10px] font-semibold tracking-widest text-slate-300 uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> En vivo
                </span>
            </div>
            <p class="text-sm text-slate-400 ml-12">Vista combinada semanal y mensual</p>
        </div>

        {{-- TARJETAS DE KPIS CON TU EFECTO HOVER Y ESFERAS DE LUZ (BLUR) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">

            {{-- PROMEDIO SEMANAL --}}
            <div class="relative overflow-hidden bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 hover:ring-indigo-500/40 transition">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                        <p class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Promedio semanal</p>
                    </div>
                    <p class="text-3xl font-bold text-white mb-2 tabular-nums">
                        {{ $weeklyAvg }}<span class="text-slate-500 text-xl font-normal ml-1">h/día</span>
                    </p>
                    <p class="text-xs text-slate-400">Semana en curso</p>
                </div>
            </div>

            {{-- PROMEDIO MENSUAL --}}
            <div class="relative overflow-hidden bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 hover:ring-emerald-500/40 transition">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <p class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Promedio mensual</p>
                    </div>
                    <p class="text-3xl font-bold text-white mb-2 tabular-nums">
                        {{ $monthlyAvg }}<span class="text-slate-500 text-xl font-normal ml-1">h/día</span>
                    </p>
                    <p class="text-xs text-slate-400">Mes en curso</p>
                </div>
            </div>

            {{-- TOTAL DÍAS TRABAJADOS --}}
            <div class="relative overflow-hidden bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 hover:ring-violet-500/40 transition">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-violet-500/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-violet-400 animate-pulse"></span>
                        <p class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Días laborados</p>
                    </div>
                    <p class="text-3xl font-bold text-white mb-2 tabular-nums">
                        {{ $totalDaysWorked ?? 0 }}<span class="text-slate-500 text-xl font-normal ml-1">días</span>
                    </p>
                    <p class="text-xs text-slate-400">Periodo evaluado</p>
                </div>
            </div>

        </div>

        {{-- FILA DE GRÁFICOS CON SECCIONES TRASLÚCIDAS INDEPENDIENTES --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- SEMANAL --}}
            <div class="relative overflow-hidden bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 hover:ring-indigo-500/40 transition">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>

                <div class="relative">
                    <h2 class="text-white font-semibold mb-4">Productividad semanal</h2>
                    <div class="h-56">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- MENSUAL --}}
            <div class="relative overflow-hidden bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 hover:ring-violet-500/40 transition">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-violet-500/10 rounded-full blur-2xl"></div>

                <div class="relative">
                    <h2 class="text-white font-semibold mb-4">Productividad mensual</h2>
                    <div class="h-56">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
document.addEventListener("DOMContentLoaded", () => {
    const weeklyLabels = JSON.parse('{!! $weeklyLabelsJson !!}');
    const weeklyData   = JSON.parse('{!! $weeklyDataJson !!}');
    const monthlyLabels = JSON.parse('{!! $monthlyLabelsJson !!}');
    const monthlyData   = JSON.parse('{!! $monthlyDataJson !!}');

    const textColor = "#cbd5e1";
    const gridColor = "rgba(255,255,255,0.03)";

    // =========================
    // GRADIENTES REALES
    // =========================

    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    const weeklyGradient = weeklyCtx.createLinearGradient(0, 0, 0, 220);
    weeklyGradient.addColorStop(0, "rgba(99, 102, 241, 0.6)");
    weeklyGradient.addColorStop(1, "rgba(99, 102, 241, 0.05)");

    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyGradient = monthlyCtx.createLinearGradient(0, 0, 0, 220);
    monthlyGradient.addColorStop(0, "rgba(139, 92, 246, 0.5)");
    monthlyGradient.addColorStop(1, "rgba(139, 92, 246, 0.05)");

    // =========================
    //  GRÁFICO SEMANAL
    // =========================
    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: weeklyLabels,
            datasets: [{
                data: weeklyData,
                backgroundColor: weeklyGradient,
                borderColor: '#6366f1',
                borderWidth: 1.5,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 32
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#0f172a",
                    borderColor: "rgba(255,255,255,0.1)",
                    borderWidth: 1,
                    titleColor: "#fff",
                    bodyColor: "#cbd5e1",
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { display: false }
                },
                y: {
                    ticks: { color: textColor },
                    grid: { color: gridColor },
                    beginAtZero: true
                }
            }
        }
    });

    // =========================
    //  GRÁFICO MENSUAL
    // =========================
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                data: monthlyData,
                borderColor: '#8b5cf6',
                backgroundColor: monthlyGradient,
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#8b5cf6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "#0f172a",
                    borderColor: "rgba(255,255,255,0.1)",
                    borderWidth: 1,
                    titleColor: "#fff",
                    bodyColor: "#cbd5e1",
                }
            },
            scales: {
                x: {
                    ticks: { color: textColor },
                    grid: { display: false }
                },
                y: {
                    ticks: { color: textColor },
                    grid: { color: gridColor },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>