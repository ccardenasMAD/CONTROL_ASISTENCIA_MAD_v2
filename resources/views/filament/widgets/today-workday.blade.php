<x-filament-widgets::widget>
    <div class="p-6 rounded-2xl bg-slate-900/70 backdrop-blur ring-1 ring-slate-700/60 shadow-xl space-y-6">

        {{-- HEADER --}}
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-1">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Mi jornada</h1>
            </div>
            <p class="text-sm text-slate-400 ml-12">Registro de asistencia en tiempo real</p>
        </div>

        {{-- TARJETAS SUPERIORES --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">

            {{-- ESTADO ACTUAL --}}
            <div class="relative overflow-hidden bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 hover:ring-blue-500/40 transition">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                        <p class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Estado actual</p>
                    </div>
                    <p class="text-2xl font-bold text-white mb-2">
                        @if($status === 'none') En espera @else {{ ucfirst(str_replace('_', ' ', $status)) }} @endif
                    </p>

                    @if ($attendance)
                        <p class="text-xs text-slate-400">
                            @if ($attendance->check_out)
                                Check-out <span class="text-red-300 font-medium">{{ \Carbon\Carbon::parse($attendance->check_out)->format('H:i') }}</span>
                            @elseif ($attendance->break_end)
                                Return <span class="text-blue-300 font-medium">{{ \Carbon\Carbon::parse($attendance->break_end)->format('H:i') }}</span>
                            @elseif ($attendance->break_start)
                                Break iniciado <span class="text-amber-300 font-medium">{{ \Carbon\Carbon::parse($attendance->break_start)->format('H:i') }}</span>
                            @elseif ($attendance->check_in)
                                Check-in <span class="text-emerald-300 font-medium">{{ \Carbon\Carbon::parse($attendance->check_in)->format('H:i') }}</span>
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            {{-- HORAS TRABAJADAS HOY --}}
            <div class="relative overflow-hidden bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 hover:ring-emerald-500/40 transition">
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <p class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold mb-3">Horas trabajadas hoy</p>
                    <p class="text-3xl font-bold text-white">
                        {{ intdiv($workedMinutes, 60) }}<span class="text-slate-500 text-xl">h</span>
                        {{ $workedMinutes % 60 }}<span class="text-slate-500 text-xl">m</span>
                    </p>

                    @if ($attendance && $attendance->break_start && !$attendance->break_end)
                        <div class="mt-3 inline-flex items-center gap-2 px-2.5 py-1 rounded-lg bg-amber-500/10 ring-1 ring-amber-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            <span class="text-xs text-amber-300 font-medium">Break activo</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- BOTÓN DINÁMICO CON LIVEWIRE (SIN RUTAS POST) --}}
            <div class="bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-5 flex flex-col justify-center">
                <p class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold mb-3">Acción rápida</p>

                @if ($action === 'CHECK_IN')
                    <button type="button"
                            onclick="sendWithGps(this, 'CHECK_IN')"
                            class="btn-animated w-full px-4 py-3 rounded-xl font-semibold text-white text-sm bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2">
                        Check-in
                    </button>

                @elseif ($action === 'BREAK')
                    <button type="button" 
                            wire:click="startBreak" {{-- Agrega este método en tu PHP si manejas break --}}
                            onclick="startLoading(this)"
                            class="btn-animated w-full px-4 py-3 rounded-xl font-semibold text-white text-sm bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2">
                        Iniciar break
                    </button>

                @elseif ($action === 'RETURN')
                    <button type="button" 
                            wire:click="endBreak" {{-- Agrega este método en tu PHP si manejas break --}}
                            onclick="startLoading(this)"
                            class="btn-animated w-full px-4 py-3 rounded-xl font-semibold text-white text-sm bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-400 hover:to-indigo-500 shadow-lg shadow-blue-500/20 flex items-center justify-center gap-2">
                        Volver del break
                    </button>

                @elseif ($action === 'NO_ACTION' && !$isCheckedOut)
                    <button type="button"
                            onclick="sendWithGps(this, 'CHECK_OUT')"
                            class="btn-animated w-full px-4 py-3 rounded-xl font-semibold text-white text-sm bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-500 hover:to-slate-600 ring-1 ring-slate-500/40 shadow-lg flex items-center justify-center gap-2">
                        Check-out
                    </button>
                @else
                    <button type="button" disabled
                            class="w-full px-4 py-3 rounded-xl font-semibold text-slate-500 text-sm bg-slate-800/50 ring-1 ring-slate-700/30 flex items-center justify-center gap-2">
                        Jornada terminada
                    </button>
                @endif
            </div>
        </div>

        {{-- TIMELINE DEL DÍA --}}
        <div class="bg-slate-900/70 ring-1 ring-slate-700/60 rounded-2xl p-6 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <h2 class="text-base font-semibold text-white">Historial del día</h2>
            </div>

            @if (count($timeline) === 0)
                <div class="text-center py-8">
                    <p class="text-sm text-slate-400">Sin registros hoy</p>
                </div>
            @else
                <ol class="relative border-l border-slate-700/60 ml-2 space-y-4">
                    @foreach ($timeline as $item)
                        @php
                            $color = str_contains(strtolower($item['label']), 'check-in') ? 'emerald'
                                : (str_contains(strtolower($item['label']), 'check-out') ? 'red'
                                : (str_contains(strtolower($item['label']), 'break') ? 'amber' : 'blue'));
                        @endphp
                        <li class="ml-5">
                            <span class="absolute -left-[7px] w-3.5 h-3.5 rounded-full bg-{{ $color }}-500 ring-4 ring-slate-900 shadow-lg shadow-{{ $color }}-500/30"></span>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-mono font-semibold text-white tabular-nums">{{ $item['time'] }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-md bg-{{ $color }}-500/10 text-{{ $color }}-300 ring-1 ring-{{ $color }}-500/20">
                                    {{ $item['label'] }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>

        {{-- CONTADOR INTELIGENTE ALPINEJS --}}
        @if ($workStart)
        <div class="bg-gradient-to-br from-slate-900/80 to-slate-900/40 ring-1 ring-slate-700/60 rounded-2xl p-6 mb-6"
             x-data="{
                start: {{ \Carbon\Carbon::parse($workStart)->timestamp * 1000 }},
                checkoutTime: {{ $checkOutTime ? $checkOutTime * 1000 : 'null' }},
                now: Date.now(),
                tick: null,
                init() { 
                    if (!this.checkoutTime) {
                        this.tick = setInterval(() => this.now = Date.now(), 1000); 
                    } else {
                        this.now = this.checkoutTime;
                    }
                },
                destroy() { if(this.tick) clearInterval(this.tick); },
                get elapsed() {
                    let s = Math.max(0, Math.floor((this.now - this.start) / 1000));
                    let h = String(Math.floor(s/3600)).padStart(2,'0');
                    let m = String(Math.floor((s%3600)/60)).padStart(2,'0');
                    let sec = String(s%60).padStart(2,'0');
                    return `${h}:${m}:${sec}`;
                }
             }">
            <div class="flex items-center justify-between mb-2">
                <p class="text-[11px] uppercase tracking-widest text-slate-400 font-semibold">Tiempo trabajado hoy</p>
                
                @if(!$isCheckedOut)
                    <span class="inline-flex items-center gap-1.5 text-xs text-emerald-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> En vivo
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs text-red-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Jornada Finalizada
                    </span>
                @endif
            </div>
            <p class="text-5xl font-bold font-mono tabular-nums bg-gradient-to-r from-white to-slate-400 bg-clip-text text-transparent" x-text="elapsed">00:00:00</p>
        </div>
        @endif

    </div>

    {{-- SCRIPTS DE GEOLOCALIZACIÓN REACCIÓN LIVEWIRE --}}
    <script>
        function startLoading(btn) {
            if (!btn || btn.disabled) return;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Procesando...';
        }

        function sendWithGps(btn, type) {
            if (btn) startLoading(btn);

            if (!navigator.geolocation) {
                alert("Tu navegador no soporta geolocalización.");
                if (btn) btn.disabled = false;
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    if (type === 'CHECK_IN') {
                        // Llama a tu método de Livewire por JavaScript si tienes handleCheckIn
                        @this.call('handleCheckIn', lat, lng);
                    } else if (type === 'CHECK_OUT') {
                        // Llama directamente a tu función PHP del widget
                        @this.call('handleCheckOut', lat, lng);
                    }
                },
                function () {
                    alert("No se pudo obtener tu ubicación. Debes permitir acceso al GPS.");
                    if (btn) { btn.disabled = false; btn.innerHTML = 'Reintentar'; }
                },
                { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
            );
        }
    </script>

    <style>
        .btn-animated { transition: all .2s ease; }
        .btn-animated:hover { transform: translateY(-1px); }
        .btn-animated:active { transform: translateY(0); }
        .spinner {
            border: 2px solid rgba(255,255,255,.3);
            border-top: 2px solid white;
            border-radius: 50%;
            width: 16px; height: 16px;
            animation: spin .8s linear infinite;
            display: inline-block; margin-right: 8px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</x-filament-widgets::widget>