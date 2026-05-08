<x-filament-panels::page>
    <div class="w-full px-4">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- PESTAÑAS SUPERIORES --}}
            <div class="flex items-center gap-10 pb-2">

                <button class="pb-3 text-sm font-bold border-b-2 border-primary-500 text-primary-500 tracking-wide uppercase">
                    Planillas
                </button>
                <button class="pb-3 text-sm font-medium text-gray-500 hover:text-gray-300 transition-colors tracking-wide uppercase">
                    Aprobaciones
                </button>
            </div>

            {{-- CABECERA --}}
            <div class="flex flex-wrap items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <h2 class="text-lg font-bold text-gray-200">Asistencia Mensual</h2>

                    {{-- Selector de Mes --}}
                    <div class="flex items-center gap-4 bg-white/5 rounded-2xl px-3 py-1.5 border border-white/10 shadow-lg">
                        <x-filament::icon-button
                            wire:click="changeMonth(-1)"
                            icon="heroicon-m-chevron-left"
                            size="sm"
                            color="gray"
                            class="hover:bg-white/10"
                        />
                        <span class="text-[12px] font-black px-4 min-w-[160px] text-center text-gray-100 uppercase tracking-[0.1em]">
                            {{ $currentMonthName }}
                        </span>
                        <x-filament::icon-button
                            wire:click="changeMonth(1)"
                            icon="heroicon-m-chevron-right"
                            size="sm"
                            color="gray"
                            class="hover:bg-white/10"
                        />
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-8 pr-10">
    <x-filament::button
        color="gray"
        outlined
        size="sm"
        icon="heroicon-m-arrow-down-tray"
        wire:click="exportPdf"
        class="border-white/10 text-gray-400 hover:bg-white/10"
    >
        PDF
    </x-filament::button>

    <x-filament::button
        color="gray"
        outlined
        size="sm"
        icon="heroicon-m-table-cells"
        wire:click="exportExcel"
        class="border-white/10 text-gray-400 hover:bg-white/10"
    >
        Excel
    </x-filament::button>
</div>


            {{-- CONTENEDOR DE FILTROS --}}
            <div class="w-full bg-[#101010]  rounded-3xl px-6 py-5 shadow-xl space-y-5">
                <div class="w-full">
                    <x-filament::input.wrapper
                        icon="heroicon-m-magnifying-glass"
                        class="bg-white/10 border-white/20 rounded-2xl shadow-inner shadow-black/30"
                    >
                        <x-filament::input
                            type="text"
                            placeholder="Buscar empleado por nombre..."
                            wire:model.live.debounce.500ms="search"
                            class="text-sm text-gray-200 placeholder:text-gray-500 border-none py-3"
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="flex flex-wrap items-center gap-x-12 gap-y-4 mt-6">


                    {{-- Filtro Payroll --}}
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 font-medium">Horas de nómina:</span>

                        <x-filament::dropdown placement="bottom-start">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 group outline-none">
                                    <span class="text-gray-200 font-semibold lowercase group-hover:text-primary-400 transition-colors">
                                        {{ $payrollType === 'all' ? 'todos' : $payrollType }}
                                    </span>
                                    <x-filament::icon
                                        icon="heroicon-m-chevron-down"
                                        class="w-4 h-4 text-gray-500 group-hover:text-primary-400"
                                    />
                                </button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item wire:click="$set('payrollType', 'all')" class="lowercase">
                                    todos
                                </x-filament::dropdown.list.item>
                                <x-filament::dropdown.list.item wire:click="$set('payrollType', 'regular')">
                                    Regular
                                </x-filament::dropdown.list.item>
                                <x-filament::dropdown.list.item wire:click="$set('payrollType', 'overtime')">
                                    Overtime
                                </x-filament::dropdown.list.item>
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                    </div>

                    {{-- Filtro Grupos --}}
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500 font-medium">Grupos:</span>

                        <x-filament::dropdown placement="bottom-start">
                            <x-slot name="trigger">
                                <button class="flex items-center gap-2 group outline-none">
                                    <span class="text-gray-200 font-semibold lowercase group-hover:text-primary-400 transition-colors">
                                        {{ $groupId ? $groups->firstWhere('id', $groupId)?->name : 'todos' }}
                                    </span>
                                    <x-filament::icon
                                        icon="heroicon-m-chevron-down"
                                        class="w-4 h-4 text-gray-500 group-hover:text-primary-400"
                                    />
                                </button>
                            </x-slot>

                            <x-filament::dropdown.list>
                                <x-filament::dropdown.list.item wire:click="$set('groupId', null)" class="lowercase">
                                    todos
                                </x-filament::dropdown.list.item>
                                @foreach($groups as $group)
                                    <x-filament::dropdown.list.item wire:click="$set('groupId', {{ $group->id }})">
                                        {{ $group->name }}
                                    </x-filament::dropdown.list.item>
                                @endforeach
                            </x-filament::dropdown.list>
                        </x-filament::dropdown>
                    </div>

                </div>
            </div>

            {{-- TABLA MAESTRA --}}
            <div class="w-full bg-white/5 border border-white/10 rounded-xl overflow-hidden shadow-sm">



                <div class="p-6">
                    <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                        <table class="w-full text-left border-separate border-spacing-0">

                            {{-- CABECERA --}}
                            <thead>
                                <tr class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">

                                    {{-- Nombre --}}
                                    <th class="p-5 sticky left-0 bg-[#0d0d0d] z-30 w-72 border-r border-white/10">
                                        Empleado
                                    </th>

                                    {{-- Días --}}
                                    @foreach($daysInMonth as $day)
                                        @php 
                                            $nombresDias = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
                                            $nombreDia = $nombresDias[$day->dayOfWeek];
                                            $esFinDeSemana = $day->isWeekend();
                                        @endphp

                                        <th class="pb-5 px-2 text-center border-b border-white/5">
                                            <span class="block text-[9px] {{ $esFinDeSemana ? 'text-red-400/50' : 'text-gray-600' }} font-bold mb-1">
                                                {{ $nombreDia }}
                                            </span>
                                            <span class="text-xs {{ $day->isToday() ? 'bg-primary-500 text-white rounded-full px-1.5 py-0.5' : 'text-gray-400' }}">
                                                {{ $day->format('j') }}
                                            </span>
                                        </th>
                                    @endforeach

                                    {{-- Total --}}
                                    <th class="p-5 text-center sticky right-0 bg-[#0d0d0d] z-30 border-l border-white/10">
                                        Total
                                    </th>
                                </tr>
                            </thead>

                            {{-- CUERPO --}}
                            <tbody class="divide-y divide-white/5">
                                @foreach($users as $user)
                                    <tr class="group hover:bg-white/[0.02] transition-colors">

                                        {{-- Nombre --}}
                                        <td class="p-4 sticky left-0 bg-[#0d0d0d] z-20 border-r border-white/10 group-hover:bg-[#111] transition-colors">
                                            <div class="flex items-center gap-4">
                                                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-primary-500/20 to-primary-600/5 flex items-center justify-center text-[11px] font-black text-primary-500 border border-primary-500/10 uppercase shadow-inner">
                                                    {{ substr($user->name, 0, 2) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-gray-200 tracking-tight group-hover:text-primary-400 transition-colors">
                                                        {{ $user->name }}
                                                    </span>
                                                    <span class="text-[10px] text-gray-500 italic">
                                                        {{ $user->roles->first()?->name ?? 'Personal' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Celdas del calendario --}}
                                        @foreach($daysInMonth as $day)
                                            @php 
                                                $attendance = $user->attendances->firstWhere('attendance_date', $day->format('Y-m-d'));

                                                $tooltipContent = "Sin registro";
                                                if ($attendance) {
                                                    $checkIn = $attendance->check_in ? date('H:i', strtotime($attendance->check_in)) : '--:--';
                                                    $checkOut = $attendance->check_out ? date('H:i', strtotime($attendance->check_out)) : '--:--';
                                                    $tooltipContent = "Entrada: {$checkIn} | Salida: {$checkOut}";
                                                } elseif ($day->isPast() && !$day->isWeekend()) {
                                                    $tooltipContent = "Ausencia injustificada";
                                                } elseif ($day->isWeekend()) {
                                                    $tooltipContent = "Fin de semana";
                                                }
                                            @endphp

                                            <td class="p-1.5 border-r border-white/5 {{ $day->isWeekend() ? 'bg-white/[0.01]' : '' }}">
                                                <div 
                                                    x-data 
                                                    x-tooltip.raw="{{ $tooltipContent }}"
                                                    wire:click="openAttendanceModal('{{ $user->id }}', '{{ $day->format('Y-m-d') }}')"
                                                    class="relative w-full h-9 rounded-md transition-all duration-200 group/cell cursor-pointer shadow-inner shadow-black/10"
                                                >
                                                    @if($attendance)
                                                        <div class="w-full h-full rounded-md bg-green-500/70 border border-green-400/20 shadow-[0_0_6px_rgba(34,197,94,0.25)] group-hover/cell:scale-105 transition-all"></div>
                                                    @elseif($day->isPast() && !$day->isWeekend())
                                                        <div class="w-full h-full rounded-md bg-red-500/10 border border-red-500/30 opacity-70 group-hover/cell:opacity-100 transition-all"></div>
                                                    @else
                                                        <div class="w-full h-full rounded-md border border-dashed border-white/10 opacity-20 group-hover/cell:opacity-40 group-hover/cell:border-primary-500/40 transition-all"></div>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach

                                        {{-- Total --}}
                                        <td class="p-4 text-center sticky right-0 bg-[#0d0d0d] z-20 border-l border-white/10 group-hover:bg-[#111] transition-colors">
                                            <div class="flex flex-col items-center">
                                                <span class="text-sm font-black text-gray-300">
                                                    {{ $user->attendances->count() * 8 }}h
                                                </span>
                                                <span class="text-[9px] text-gray-600 font-medium italic">0m</span>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>

                            {{-- PIE --}}
                            <tfoot class="bg-white/[0.02]">
                                <tr class="border-t border-white/10">
                                    <td class="p-5 sticky left-0 bg-[#0d0d0d] z-30 border-r border-white/10 text-[11px] font-black text-gray-500 uppercase tracking-widest">
                                        Total Presentes
                                    </td>

                                    @foreach($daysInMonth as $day)
                                        @php
                                            $totalDia = $users->filter(fn($u) => $u->attendances->contains('attendance_date', $day->format('Y-m-d')))->count();
                                        @endphp
                                        <td class="p-2 text-center border-r border-white/5">
                                            <span class="text-[11px] font-bold {{ $totalDia > 0 ? 'text-primary-500' : 'text-gray-700' }}">
                                                {{ $totalDia }}
                                            </span>
                                        </td>
                                    @endforeach

                                    <td class="p-5 sticky right-0 bg-[#0d0d0d] z-30 border-l border-white/10"></td>
                                </tr>
                            </tfoot>

                        </table>
                    </div>
                </div>
            </div>

            {{-- LEYENDA --}}
            <div class="w-full max-w-7xl mx-auto mt-2">
            <div class="flex flex-wrap items-center gap-6 px-4 py-4 bg-white/5 border border-white/10 rounded-xl shadow-sm">

                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-md bg-green-500/80 border border-green-400/20"></div>
                        <span class="text-[11px] text-gray-400 font-semibold uppercase tracking-widest">Presente</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-md bg-red-500/20 border border-red-500/40"></div>
                        <span class="text-[11px] text-gray-400 font-semibold uppercase tracking-widest">Ausencia</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-md border border-dashed border-white/20"></div>
                        <span class="text-[11px] text-gray-400 font-semibold uppercase tracking-widest">Vacaciones</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-4 h-4 rounded-md border border-dashed border-white/20"></div>
                        <span class="text-[11px] text-gray-400 font-semibold uppercase tracking-widest">Sin Registro / Futuro</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-filament-panels::page>
