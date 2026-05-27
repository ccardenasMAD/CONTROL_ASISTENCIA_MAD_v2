<x-filament-panels::page>
    <div class="space-y-6">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 pb-5 border-b border-slate-800">
            <div>
                <h1 class="text-3xl font-bold text-slate-50 tracking-tight">
                    {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
                </h1>
                <p class="text-sm text-slate-400 mt-1">Resumen mensual de asistencia</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" wire:click="previousMonth"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-200 ring-1 ring-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Mes anterior
                </button>

                <button type="button" wire:click="nextMonth"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white shadow-lg shadow-blue-600/20 transition">
                    Mes siguiente
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- FILTROS --}}
        <div class="flex flex-col md:flex-row gap-3">
            <div class="relative w-full md:w-80">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar empleado..."
                    class="w-full pl-9 pr-4 py-2 rounded-lg bg-slate-900 ring-1 ring-slate-700 text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-blue-500 outline-none transition" />
            </div>

            <select wire:model.live="groupId"
                class="w-full md:w-64 px-4 py-2 rounded-lg bg-slate-900 ring-1 ring-slate-700 text-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                <option value="">Todos los grupos</option>
                @foreach(App\Models\Group::all() as $group)
                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- LEYENDA --}}
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-400">
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-emerald-500"></span> Presente</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-amber-500"></span> Tarde / Salida Temprana</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-red-500"></span> Ausente</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-pink-500"></span> Vacaciones</div>
            <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-slate-700 ring-1 ring-slate-600"></span> Sin registro</div>
        </div>

        {{-- TABLA --}}
        <div class="overflow-x-auto rounded-xl ring-1 ring-slate-800 bg-slate-900/60 shadow-xl shadow-black/20">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <thead>
                    <tr class="bg-gradient-to-b from-slate-900 to-slate-950">
                        <th class="sticky left-0 z-20 bg-slate-950 px-4 py-3 text-left font-semibold text-blue-400 uppercase text-[11px] tracking-wider border-b border-slate-800">
                            Empleado
                        </th>

                        @foreach($days as $day => $info)
                            @php
                                $isWeekend = in_array($info['weekday_full'], ['Sábado', 'Domingo']);
                                $headBg    = $isWeekend ? 'bg-slate-900/80' : '';
                                $wkColor   = $isWeekend ? 'text-red-400' : 'text-slate-500';
                                $dayColor  = $isWeekend ? 'text-red-300' : 'text-slate-100';
                            @endphp
                            <th class="px-2 py-3 text-center font-medium border-b border-slate-800 {{ $headBg }}">
                                <div class="text-[10px] uppercase tracking-wider {{ $wkColor }}">{{ $info['weekday'] }}</div>
                                <div class="text-sm font-bold {{ $dayColor }} mt-0.5">{{ $day }}</div>
                            </th>
                        @endforeach

                        <th class="px-4 py-3 text-right font-semibold text-sky-400 uppercase text-[11px] tracking-wider border-b border-slate-800">
                            Total
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($usersData as $i => $user)
                        @php
                            $rowBg = $i % 2 === 0 ? 'bg-slate-900/40' : 'bg-slate-900/10';
                            $palette = ['from-blue-500 to-indigo-600', 'from-emerald-500 to-teal-600', 'from-amber-500 to-orange-600', 'from-pink-500 to-rose-600', 'from-violet-500 to-purple-600'];
                            $grad = $palette[crc32($user['name']) % count($palette)];
                        @endphp
                        <tr class="{{ $rowBg }} hover:bg-slate-800/40 transition">
                            <td class="sticky left-0 z-10 bg-slate-950 px-4 py-3 border-b border-slate-800/60">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $grad }} text-white flex items-center justify-center text-xs font-bold shadow-md">
                                        {{ strtoupper(substr($user['name'], 0, 1)) }}
                                    </div>
                                    <span class="text-slate-100 font-medium whitespace-nowrap">{{ $user['name'] }}</span>
                                </div>
                            </td>

                            @foreach($days as $day => $info)
                                @php
                                    $isWeekend = in_array($info['weekday_full'], ['Sábado', 'Domingo']);
                                    $cellBg    = $isWeekend ? 'bg-slate-900/60' : '';
                                    $date = $info['date'];
                                    $cell = $user['calendar'][$date] ?? ['status' => 'none'];
                                    
                                    // Mapeo forzado directo en HTML para evitar que el optimizador de Tailwind destruya los colores
                                    $colorClass = match($cell['status']) {
                                        'present' => 'bg-emerald-500 hover:bg-emerald-400',
                                        'late', 'early_exit' => 'bg-amber-500 hover:bg-amber-400',
                                        'absent' => 'bg-red-500 hover:bg-red-400',
                                        'vacation' => 'bg-pink-500 hover:bg-pink-400',
                                        default => 'bg-slate-700/60 hover:bg-slate-600',
                                    };
                                @endphp
                                <td class="px-2 py-2 text-center border-b border-slate-800/60 {{ $cellBg }}">
                                    <button type="button"
                                        wire:click="openAttendanceModal({{ $user['id'] }}, '{{ $date }}')"
                                        x-on:click="$dispatch('open-modal', { id: 'attendanceModal' })"
                                        class="w-7 h-7 mx-auto rounded-md transition hover:scale-110 hover:ring-2 hover:ring-blue-400/60 ring-1 ring-slate-700/60 {{ $colorClass }}"
                                        title="{{ $cell['status'] }}"></button>
                                </td>
                            @endforeach

                            <td class="px-4 py-3 text-right border-b border-slate-800/60">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-sky-500/10 text-sky-300 ring-1 ring-sky-500/20 font-semibold text-xs">
                                    {{ is_numeric($user['totalMinutes']) ? intdiv($user['totalMinutes'], 60).'h '.($user['totalMinutes'] % 60).'m' : '0h 0m' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{--MODAL--}}
    <x-filament::modal id="attendanceModal" width="md">
        <x-slot name="heading">
            <span class="text-lg font-bold text-blue-400">Detalles de Asistencia</span>
        </x-slot>

        @if($modalData)
            @php
                $status = $modalData['status'];
                $a = $modalData['attendance'] ?? null;
                
                // Evitamos el formateo sobre propiedades nulas
                $checkIn    = ($a && $a->check_in) ? \Carbon\Carbon::parse($a->check_in)->format('H:i') : '—';
                $checkOut   = ($a && $a->check_out) ? \Carbon\Carbon::parse($a->check_out)->format('H:i') : '—';
                $breakStart = ($a && $a->break_start) ? \Carbon\Carbon::parse($a->break_start)->format('H:i') : '—';
                $breakEnd   = ($a && $a->break_end) ? \Carbon\Carbon::parse($a->break_end)->format('H:i') : '—';
                
                $workedValue = $modalData['worked'] ?? 0;
            @endphp

            <div class="space-y-4">
                <div class="text-base font-semibold text-slate-100">{{ $modalData['user'] }}</div>

                <dl class="grid grid-cols-2 gap-y-2 text-sm">
                    <dt class="text-slate-400">Fecha:</dt>
                    <dd class="text-slate-200">{{ \Carbon\Carbon::parse($modalData['date'])->translatedFormat('d F Y') }}</dd>

                    <dt class="text-slate-400">Estado:</dt>
                    <dd><span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-300 ring-1 ring-blue-500/20 text-xs">{{ $status }}</span></dd>

                    <dt class="text-slate-400">Entrada:</dt>
                    <dd class="text-emerald-400 font-medium">{{ $checkIn }}</dd>

                    <dt class="text-slate-400">Salida:</dt>
                    <dd class="text-amber-400 font-medium">{{ $checkOut }}</dd>

                    <dt class="text-slate-400">Break inicio:</dt>
                    <dd class="text-slate-200">{{ $breakStart }}</dd>

                    <dt class="text-slate-400">Break fin:</dt>
                    <dd class="text-slate-200">{{ $breakEnd }}</dd>

                    <dt class="text-slate-400">Total:</dt>
                    <dd class="text-sky-400 font-bold">
                        @if(is_numeric($workedValue))
                            {{ intdiv($workedValue, 60) }}h {{ $workedValue % 60 }}m
                        @else
                            {{ $workedValue }}
                        @endif
                    </dd>
                </dl>
            </div>
        @endif

        <x-slot name="footer">
            <button x-on:click="$dispatch('close-modal', { id: 'attendanceModal' })"
                class="w-full px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 ring-1 ring-slate-700 transition">
                Cerrar
            </button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>