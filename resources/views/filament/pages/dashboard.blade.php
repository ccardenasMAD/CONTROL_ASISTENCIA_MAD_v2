<x-filament-panels::page>

    {{-- TOASTS VISUALES --}}
    @if (session('success'))
        <div class="fixed top-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg animate-fade">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fixed top-4 right-4 bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg animate-fade">
            {{ session('error') }}
        </div>
    @endif

    <style>
        .animate-fade {
            animation: fadeOut 4s forwards;
        }
        @keyframes fadeOut {
            0% { opacity: 1; }
            80% { opacity: 1; }
            100% { opacity: 0; transform: translateY(-10px); }
        }
    </style>

    {{-- TARJETAS SUPERIORES --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- ESTADO ACTUAL --}}
        <div class="p-5 bg-gray-800 rounded-xl shadow-lg text-white">
            <h3 class="text-sm uppercase tracking-wide text-gray-400">Estado actual</h3>
            <p class="text-3xl font-bold mt-2 capitalize">
                {{ $status }}
            </p>

            @if ($attendance)
                <p class="text-xs text-gray-400 mt-1">
                    Última acción:
                    <span class="text-gray-200">
                        @if ($attendance->check_out)
                            Check-out a las {{ $attendance->check_out->format('H:i') }}
                        @elseif ($attendance->break_end)
                            Return a las {{ $attendance->break_end->format('H:i') }}
                        @elseif ($attendance->break_start)
                            Break iniciado a las {{ $attendance->break_start->format('H:i') }}
                        @elseif ($attendance->check_in)
                            Check-in a las {{ $attendance->check_in->format('H:i') }}
                        @endif
                    </span>
                </p>
            @endif
        </div>

        {{-- HORAS TRABAJADAS HOY --}}
        <div class="p-5 bg-gray-800 rounded-xl shadow-lg text-white">
            <h3 class="text-sm uppercase tracking-wide text-gray-400">Horas trabajadas hoy</h3>
            <p class="text-3xl font-bold mt-2">
                {{ intdiv($workedMinutes, 60) }}h {{ $workedMinutes % 60 }}m
            </p>

            @if ($attendance && $attendance->break_start && !$attendance->break_end)
                <div class="mt-2">
                    <p class="text-xs text-yellow-400">
                        Break activo:
                        <span id="break-timer" class="text-yellow-300 font-bold"></span>
                    </p>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const breakStart = new Date("{{ $attendance->break_start }}").getTime();

                        function updateBreakTimer() {
                            const now = new Date().getTime();
                            const diff = now - breakStart;

                            const hours = Math.floor(diff / (1000 * 60 * 60));
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                            const formatted =
                                String(hours).padStart(2, '0') + ":" +
                                String(minutes).padStart(2, '0') + ":" +
                                String(seconds).padStart(2, '0');

                            const el = document.getElementById('break-timer');
                            if (el) {
                                el.textContent = formatted;
                            }
                        }

                        updateBreakTimer();
                        setInterval(updateBreakTimer, 1000);
                    });
                </script>
            @endif
        </div>

        {{-- BOTÓN DINÁMICO --}}
        <div class="p-5 bg-gray-800 rounded-xl shadow-lg text-white flex items-center justify-center">

            <script>
                function startLoading(btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner"></span>Procesando...';
                }
            </script>

            @if ($action === 'CHECK_IN')
                <form method="POST" action="{{ route('attendance.checkin') }}" class="w-full">
                    @csrf
                    <button onclick="startLoading(this)"
                            class="pulse btn-animated w-full px-4 py-3 bg-green-600 rounded-lg font-bold text-white text-lg">
                        Check-in
                    </button>
                </form>

            @elseif ($action === 'BREAK')
                <form method="POST" action="{{ route('attendance.break.start') }}" class="w-full">
                    @csrf
                    <button onclick="startLoading(this)"
                            class="pulse btn-animated w-full px-4 py-3 bg-yellow-500 rounded-lg font-bold text-white text-lg">
                        Iniciar break
                    </button>
                </form>

            @elseif ($action === 'RETURN')
                <form method="POST" action="{{ route('attendance.break.end') }}" class="w-full">
                    @csrf
                    <button onclick="startLoading(this)"
                            class="pulse btn-animated w-full px-4 py-3 bg-blue-500 rounded-lg font-bold text-white text-lg">
                        Volver del break
                    </button>
                </form>

            @elseif ($action === 'NO_ACTION')
                <form method="POST" action="{{ route('attendance.checkout') }}" class="w-full">
                    @csrf
                    <button onclick="startLoading(this)"
                            class="pulse btn-animated w-full px-4 py-3 bg-gray-500 rounded-lg font-bold text-white text-lg">
                        Check-out
                    </button>
                </form>
            @endif

        </div>

    </div>

    {{-- TIMELINE DEL DÍA --}}
    <div class="p-5 bg-gray-800 rounded-xl shadow-lg text-white mb-6">
        <h3 class="text-sm uppercase tracking-wide text-gray-400 mb-3">Historial del día</h3>

        @if (count($timeline) === 0)
            <p class="text-gray-400 text-sm">Sin registros hoy.</p>
        @else
            <ul class="space-y-2">
                @foreach ($timeline as $item)
                    <li class="flex items-center gap-3">
                        <span class="text-lg font-bold {{ $item['color'] }}">
                            ●
                        </span>
                        <span class="text-gray-200 font-semibold">{{ $item['time'] }}</span>
                        <span class="text-gray-400">{{ $item['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- CONTADOR DE JORNADA --}}
    @if ($workStart)
    <div class="p-5 bg-gray-800 rounded-xl shadow-lg text-white mb-6">
        <h3 class="text-sm uppercase tracking-wide text-gray-400">Tiempo trabajado hoy</h3>
        <p id="workday-timer" class="text-3xl font-bold mt-2"></p>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const start = new Date("{{ $workStart }}").getTime();

                function updateWorkdayTimer() {
                    const now = new Date().getTime();
                    const diff = now - start;

                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    const formatted =
                        String(hours).padStart(2, '0') + ":" +
                        String(minutes).padStart(2, '0') + ":" +
                        String(seconds).padStart(2, '0');

                    document.getElementById('workday-timer').textContent = formatted;
                }

                updateWorkdayTimer();
                setInterval(updateWorkdayTimer, 1000);
            });
        </script>
    </div>
    @endif

    {{-- ⭐ AQUI SE RENDERIZAN LOS WIDGETS DE FILAMENT --}}
    <x-filament-widgets::widgets />

    {{-- ESTILOS DE ANIMACIÓN --}}
    <style>
        .pulse {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .btn-animated:hover {
            transform: translateY(-2px);
            transition: 0.2s ease-in-out;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
        }

        .spinner {
            border: 3px solid rgba(255,255,255,0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            animation: spin 0.8s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

</x-filament-panels::page>
