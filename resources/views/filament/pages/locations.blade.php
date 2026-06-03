<x-filament-panels::page>

    {{-- ENCABEZADO --}}
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-white tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 11a3 3 0 100-6 3 3 0 000 6z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 10.5c0 6-7.5 11-7.5 11s-7.5-5-7.5-11a7.5 7.5 0 1115 0z"/>
                </svg>
                Locations
            </h1>
            <p class="mt-1 text-sm text-slate-400">
                Mapa de check-in y check-out del personal
            </p>
        </div>

        {{-- LEYENDA --}}
        <div class="hidden md:flex items-center gap-4 text-xs text-slate-300 bg-slate-900/60 ring-1 ring-slate-700/60 rounded-lg px-3 py-2">
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-emerald-500/30"></span>
                Check-in
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 ring-2 ring-red-500/30"></span>
                Check-out
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-4 h-0.5 bg-amber-400" style="background-image:linear-gradient(to right, #fbbf24 50%, transparent 50%); background-size:6px 2px;"></span>
                Trayecto
            </span>
        </div>
    </div>

    {{-- FILTROS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

        {{-- FECHA --}}
        <div class="bg-slate-900/70 hover:bg-slate-900/80 transition-colors ring-1 ring-slate-700/60 rounded-xl p-5">
            <label class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Fecha
            </label>
            <input
                type="date"
                wire:model.live="date"
                class="w-full bg-slate-950/60 border-0 ring-1 ring-slate-700 focus:ring-2 focus:ring-blue-500 rounded-lg px-3 py-2 text-sm text-slate-100"
            />
        </div>

        {{-- EMPLEADO --}}
        <div class="bg-slate-900/70 hover:bg-slate-900/80 transition-colors ring-1 ring-slate-700/60 rounded-xl p-5">
            <label class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-widest text-slate-400 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8zm-7 9a7 7 0 0114 0H5z"/>
                </svg>
                Empleado
            </label>
            <select
                wire:model.live="userId"
                class="w-full bg-slate-950/60 border-0 ring-1 ring-slate-700 focus:ring-2 focus:ring-blue-500 rounded-lg px-3 py-2 text-sm text-slate-100"
            >
                <option value="">Todos</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- ESTADO --}}
        <div class="bg-slate-900/70 hover:bg-slate-900/80 transition-colors ring-1 ring-slate-700/60 rounded-xl p-5 relative">
            <div class="flex items-center justify-between mb-3">
                <label class="flex items-center gap-2 text-[11px] font-semibold uppercase tracking-widest text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 12h12M10 20h4"/>
                    </svg>
                    Estado
                </label>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-blue-500/15 text-blue-300 ring-1 ring-blue-500/30">SOON</span>
            </div>
            <select disabled class="w-full bg-slate-800/40 ring-1 ring-slate-700/40 rounded-lg px-3 py-2 text-sm text-slate-500 cursor-not-allowed">
                <option>Próximamente...</option>
            </select>
        </div>

    </div>

    {{-- CONTENIDO --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- MAPA --}}
        <div class="lg:col-span-1">
            <div class="relative bg-slate-900 ring-1 ring-slate-700/60 rounded-3xl overflow-hidden shadow-3xl shadow-black/40">
            <div id="locations-map" class="z-0 h-[calc(100vh-180px)] w-full"></div>
                <div class="pointer-events-none absolute inset-x-0 top-0 h-10 bg-gradient-to-b from-slate-950/40 to-transparent z-10"></div>
            </div>
        </div>

        {{-- PANEL LATERAL --}}
<div class="lg:col-span-1">
    <div class="bg-slate-900/70 ring-1 ring-slate-700/60 rounded-3xl overflow-hidden flex flex-col h-full">

        {{-- HEADER PANEL --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-sm font-semibold text-white">Registros del día</h3>
            </div>
            <span class="text-xs font-medium text-blue-300 bg-blue-500/10 ring-1 ring-blue-500/20 px-2 py-0.5 rounded-full">
                {{ $attendances->count() }}
            </span>
        </div>

        {{-- CONTENIDO SCROLLABLE --}}
        <div class="flex-1 overflow-y-auto p-4 space-y-4">

            {{-- LISTA --}}
            @if ($attendances->isEmpty())
                <div class="text-center py-10 text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 20l-5.447-2.724A2 2 0 013 15.382V5.618a2 2 0 012.382-1.894L9 5m0 15l6-3m-6 3V5m6 12l5.447 2.724A2 2 0 0021 17.382V7.618a2 2 0 00-1.382-1.894L15 4m0 13V4m0 0L9 5"/>
                    </svg>
                    <p class="text-sm">No hay registros con ubicación para estos filtros.</p>
                </div>
            @else
                @foreach ($attendances as $attendance)
                    <div class="group bg-slate-950/60 hover:bg-slate-900 transition-all duration-200 ring-1 ring-slate-800 hover:ring-blue-500/40 rounded-xl p-4 cursor-pointer">

                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-xs font-semibold text-white shrink-0 ring-2 ring-blue-500/40 shadow-md">
                                    {{ strtoupper(substr($attendance->user->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-slate-100 truncate">
                                    {{ $attendance->user->name }}
                                </span>
                            </div>
                            <span class="text-[11px] font-medium text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded shrink-0">
                                {{ $attendance->attendance_date->format('d/m/Y') }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div class="flex items-center gap-2 bg-emerald-500/5 ring-1 ring-emerald-500/15 rounded-lg px-2.5 py-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-emerald-500/30"></span>
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[10px] uppercase tracking-wide text-emerald-400/80">Check-in</span>
                                    <span class="text-xs font-semibold text-emerald-300">
                                        {{ $attendance->check_in ? $attendance->check_in->format('H:i') : '—' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 bg-red-500/5 ring-1 ring-red-500/15 rounded-lg px-2.5 py-1.5">
                                <span class="w-2 h-2 rounded-full bg-red-500 ring-2 ring-red-500/30"></span>
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[10px] uppercase tracking-wide text-red-400/80">Check-out</span>
                                    <span class="text-xs font-semibold text-red-300">
                                        {{ $attendance->check_out ? $attendance->check_out->format('H:i') : '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            @endif

            {{-- CUADRO 2: RESUMEN DEL DÍA --}}
            <div class="border-t border-slate-800 pt-4">
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">
                    Resumen del día
                </h4>

                <div class="grid grid-cols-3 gap-3">

                    <div class="bg-slate-950/60 ring-1 ring-slate-800 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wide">Check-ins</p>
                        <p class="text-lg font-bold text-emerald-400">
                            {{ $attendances->whereNotNull('check_in')->count() }}
                        </p>
                    </div>

                    <div class="bg-slate-950/60 ring-1 ring-slate-800 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wide">Check-outs</p>
                        <p class="text-lg font-bold text-red-400">
                            {{ $attendances->whereNotNull('check_out')->count() }}
                        </p>
                    </div>

                    <div class="bg-slate-950/60 ring-1 ring-slate-800 rounded-xl p-3 text-center">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wide">Registros</p>
                        <p class="text-lg font-bold text-blue-400">
                            {{ $attendances->count() }}
                        </p>
                    </div>

                </div>
            </div>
            {{-- CUADRO 3: ESTADO DETALLADO  --}}
<div class="border-t border-slate-800 pt-4">
    <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">
        Estado detallado
    </h4>

    <div class="bg-slate-950/60 ring-1 ring-slate-800 rounded-2xl p-4 text-sm space-y-5">

        {{-- CHECK-IN --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-emerald-500/30"></span>
                <p class="text-xs font-bold text-emerald-400 tracking-wide">CHECK-IN</p>
            </div>

            @php
                $checkIn = $attendances->filter(fn($a) => $a->getLocationStatus() === 'in');
            @endphp

            @if($checkIn->isEmpty())
                <p class="text-slate-500 text-xs ml-4">Nadie ha hecho check-in.</p>
            @else
                <ul class="ml-4 space-y-1">
                    @foreach($checkIn as $a)
                        <li class="text-slate-300">{{ $a->user->name }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- CHECK-OUT --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 ring-2 ring-red-500/30"></span>
                <p class="text-xs font-bold text-red-400 tracking-wide">CHECK-OUT</p>
            </div>

            @php
                $checkOut = $attendances->filter(fn($a) => $a->getLocationStatus() === 'out');
            @endphp

            @if($checkOut->isEmpty())
                <p class="text-slate-500 text-xs ml-4">Nadie ha hecho check-out.</p>
            @else
                <ul class="ml-4 space-y-1">
                    @foreach($checkOut as $a)
                        <li class="text-slate-300">{{ $a->user->name }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- BREAK --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 ring-2 ring-yellow-400/30"></span>
                <p class="text-xs font-bold text-yellow-400 tracking-wide">BREAK</p>
            </div>

            @php
                $break = $attendances->filter(fn($a) => $a->getLocationStatus() === 'break');
            @endphp

            @if($break->isEmpty())
                <p class="text-slate-500 text-xs ml-4">Nadie está en break.</p>
            @else
                <ul class="ml-4 space-y-1">
                    @foreach($break as $a)
                        <li class="text-slate-300">{{ $a->user->name }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- AUSENTES --}}
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-500 ring-2 ring-slate-500/30"></span>
                <p class="text-xs font-bold text-slate-400 tracking-wide">AUSENTES</p>
            </div>

            @php
                $presentUserIds = $attendances->pluck('user_id')->unique();
                $absentUsers = $users->whereNotIn('id', $presentUserIds);
            @endphp

            @if($absentUsers->isEmpty())
                <p class="text-slate-500 text-xs ml-4">No hay ausentes.</p>
            @else
                <ul class="ml-4 space-y-1">
                    @foreach($absentUsers as $u)
                        <li class="text-slate-300">{{ $u->name }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</div>


    {{-- LEAFLET --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #locations-map .leaflet-control-zoom a {
            background: #1e293b !important;
            color: #e2e8f0 !important;
            border: 1px solid #334155 !important;
        }
        #locations-map .leaflet-control-zoom a:hover {
            background: #2563eb !important;
            color: #fff !important;
        }
        #locations-map .leaflet-popup-content-wrapper {
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 10px;
            border: 1px solid #334155;
            box-shadow: 0 10px 30px rgba(0,0,0,.4);
        }
        #locations-map .leaflet-popup-tip { background: #0f172a; }
        #locations-map .leaflet-popup-content { font-size: 12px; line-height: 1.4; }
        #locations-map .leaflet-popup-content strong { color: #60a5fa; }
        #locations-map .leaflet-control-attribution {
            background: rgba(15,23,42,.7) !important;
            color: #94a3b8 !important;
        }
        #locations-map .leaflet-control-attribution a { color: #60a5fa !important; }
    </style>

    <script>
        let map = null;
        let markersLayer = null;

        document.addEventListener('livewire:navigated', function () {
            if (map !== null) return;

            map = L.map('locations-map', {
                zoomControl: false,
                scrollWheelZoom: true,
            }).setView([-33.45, -70.66], 12);

            L.control.zoom({ position: 'bottomright' }).addTo(map);
            markersLayer = L.layerGroup().addTo(map);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
            }).addTo(map);

            function makeDot(color) {
                return L.divIcon({
                    className: '',
                    html: `
                        <span style="
                            position: relative;
                            display: inline-block;
                            width: 18px; height: 18px;
                            border-radius: 9999px;
                            background: ${color};
                            box-shadow: 0 0 0 4px ${color}33, 0 2px 6px rgba(0,0,0,.4);
                            border: 2px solid #fff;
                        "></span>`,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9],
                    popupAnchor: [0, -10],
                });
            }

            function renderMarkers(data) {
                markersLayer.clearLayers();
                if (!data || data.length === 0) return;

                let bounds = [];

                data.forEach(function (item) {
                    if (item.check_in_lat && item.check_in_lng) {
                        L.marker([item.check_in_lat, item.check_in_lng], { icon: makeDot('#10b981') })
                            .bindPopup(`
                                <strong>${item.user_name}</strong><br>
                                <span style="color:#34d399">● Check-in:</span> ${item.check_in_time}<br>
                                <span style="color:#94a3b8">${item.date}</span>
                            `)
                            .addTo(markersLayer);

                        bounds.push([item.check_in_lat, item.check_in_lng]);
                    }

                    if (item.check_out_lat && item.check_out_lng) {
                        L.marker([item.check_out_lat, item.check_out_lng], { icon: makeDot('#ef4444') })
                            .bindPopup(`
                                <strong>${item.user_name}</strong><br>
                                <span style="color:#f87171">● Check-out:</span> ${item.check_out_time}<br>
                                <span style="color:#94a3b8">${item.date}</span>
                            `)
                            .addTo(markersLayer);

                        bounds.push([item.check_out_lat, item.check_out_lng]);
                    }

                    if (item.check_in_lat && item.check_out_lat) {
                        L.polyline(
                            [
                                [item.check_in_lat, item.check_in_lng],
                                [item.check_out_lat, item.check_out_lng],
                            ],
                            {
                                color: '#fbbf24',
                                weight: 3,
                                dashArray: '6,6',
                                opacity: 0.8,
                            }
                        ).addTo(markersLayer);
                    }
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            }

            renderMarkers(@json($locations));

            Livewire.on('refreshLocationsMap', data => renderMarkers(data));
        });
    </script>

</x-filament-panels::page>
