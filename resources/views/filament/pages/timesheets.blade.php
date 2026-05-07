<x-filament-panels::page>
    <div class="w-full space-y-8 antialiased px-4">
        
        {{-- Pestañas superiores con más aire --}}
        <div class="flex items-center gap-15 border-b border-white/10 w-full">
            <button class="pb-4 text-sm font-bold border-b-2 border-primary-500 text-primary-500 tracking-wide uppercase">Planillas</button>
            <button class="pb-4 text-sm font-medium text-gray-500 hover:text-gray-300 transition-colors tracking-wide uppercase">Aprobaciones</button>
        </div>

        {{-- Cabecera: Título y Navegación --}}
        <div class="flex flex-wrap items-center justify-between gap-10">
            <div class="flex items-center gap-8">
                <h2 class="text-lg font-bold text-gray-200">Asistencia Mensual</h2>
                
                {{-- Selector de Mes --}}
                <div class="flex items-center gap-5 bg-white/5 rounded-xl p-1.8 border border-white/10 shadow-lg">
                    <x-filament::icon-button wire:click="changeMonth(-1)" icon="heroicon-m-chevron-left" size="sm" color="gray" class="hover:bg-white/10" />
                    <span class="text-[12px] font-black px-4 min-w-[160px] text-center text-gray-100 uppercase tracking-[0.1em]">
                        {{ $currentMonthName }}
                    </span>
                    <x-filament::icon-button wire:click="changeMonth(1)" icon="heroicon-m-chevron-right" size="sm" color="gray" class="hover:bg-white/15" />
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <x-filament::button color="gray" outlined size="sm" icon="heroicon-m-arrow-down-tray" wire:click="exportPdf" class="border-white/10 text-gray-400 hover:bg-white/10">PDF</x-filament::button>
                <x-filament::button color="gray" outlined size="sm" icon="heroicon-m-table-cells" wire:click="exportExcel" class="border-white/10 text-gray-400 hover:bg-white/10">Excel</x-filament::button>
            </div>
        </div>

        {{-- Barra de Filtros: Limpia y sin mayúsculas agresivas --}}
        <div class="grid grid-cols-1 md:flex items-center gap-8 py-2">
            <div class="w-full md:w-96">
                <x-filament::input.wrapper icon="heroicon-m-magnifying-glass" class="bg-white/5 border-white/10 shadow-none ring-0 overflow-hidden rounded-xl">
                    <x-filament::input type="text" placeholder="Buscar empleado por nombre..." wire:model.live.debounce.500ms="search" class="text-sm text-gray-300 placeholder:text-gray-600 border-none py-2.5" />
                </x-filament::input.wrapper>
            </div>

            <div class="flex flex-wrap items-center gap-10 text-[13px]">
                {{-- Filtro Payroll --}}
                <x-filament::dropdown placement="bottom-start">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 group outline-none">
                            <span class="text-gray-500 font-medium">Horas de nómina:</span>
                            <span class="text-gray-200 font-bold group-hover:text-primary-400 transition-colors lowercase">{{ $payrollType === 'all' ? 'todos' : $payrollType }}</span>
                            <x-filament::icon icon="heroicon-m-chevron-down" class="w-4 h-4 text-gray-600 group-hover:text-primary-400" />
                        </button>
                    </x-slot>
                    <x-filament::dropdown.list>
                        <x-filament::dropdown.list.item wire:click="$set('payrollType', 'all')" class="lowercase">todos</x-filament::dropdown.list.item>
                        <x-filament::dropdown.list.item wire:click="$set('payrollType', 'regular')">Regular</x-filament::dropdown.list.item>
                        <x-filament::dropdown.list.item wire:click="$set('payrollType', 'overtime')">Overtime</x-filament::dropdown.list.item>
                    </x-filament::dropdown.list>
                </x-filament::dropdown>

                {{-- Filtro Grupos --}}
                <x-filament::dropdown placement="bottom-start">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 group outline-none">
                            <span class="text-gray-500 font-medium">Grupos:</span>
                            <span class="text-gray-200 font-bold group-hover:text-primary-400 transition-colors lowercase">{{ $groupId ? $groups->firstWhere('id', $groupId)?->name : 'todos' }}</span>
                            <x-filament::icon icon="heroicon-m-chevron-down" class="w-4 h-4 text-gray-600 group-hover:text-primary-400" />
                        </button>
                    </x-slot>
                    <x-filament::dropdown.list>
                        <x-filament::dropdown.list.item wire:click="$set('groupId', null)" class="lowercase">todos</x-filament::dropdown.list.item>
                        @foreach($groups as $group)
                            <x-filament::dropdown.list.item wire:click="$set('groupId', {{ $group->id }})">{{ $group->name }}</x-filament::dropdown.list.item>
                        @endforeach
                    </x-filament::dropdown.list>
                </x-filament::dropdown>
            </div>
        </div>

        {{-- Tabla Maestra: Con estructura y traducción --}}
        <div class="mt-12 px-8 pb-12">
        <div class="w-full bg-[#0d0d0d] border border-white/10 rounded-3xl overflow-hidden shadow-2xl p-6">
        <div class="p-10">
            <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
                <table class="w-full text-left border-separate border-spacing-0">
            
                    <thead>
                    <tr class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em]">
                            {{-- Cabecera Nombre --}}
                            <th class="p-6 sticky left-3 bg-[#0d0d0d] z-30 w-72 border-r border-white/10 text-[12px] font-black text-gray-500 uppercase tracking-widest">
                                Empleado
                            </th>

                            
                            
                            {{-- Días de la semana en ESPAÑOL --}}
                            @foreach($daysInMonth as $day)
                                @php 
                                    $nombresDias = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
                                    $nombreDia = $nombresDias[$day->dayOfWeek];
                                    $esFinDeSemana = $day->isWeekend();
                                @endphp
                                
                                <th class="pb-8 px-2 text-center border-b border-white/5">
                                    <span class="block text-[9px] {{ $esFinDeSemana ? 'text-red-400/50' : 'text-gray-600' }} font-bold mb-1">{{ $nombreDia }}</span>
                                    <span class="text-xs {{ $day->isToday() ? 'bg-primary-500 text-white rounded-full px-1.5 py-0.5' : 'text-gray-400' }}">{{ $day->format('j') }}</span>
                                </th>
                            @endforeach

                            <th class="p-5 text-center sticky right-0 bg-[#0d0d0d] z-30 border-l border-white/10 text-[11px] font-black text-gray-500 uppercase tracking-widest">
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($users as $user)
                            <tr class="group hover:bg-white/[0.01] transition-colors">
                                {{-- Celda de Nombre Fija --}}
                                <td class="p-4 sticky left-0 bg-[#0d0d0d] z-20 border-r border-radi-white/15  group-hover:bg-[#121212] transition-colors">
                                    <div class="flex items-center gap-4">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-primary-500/20 to-primary-600/5 flex items-center justify-center text-[11px] font-black text-primary-500 border border-primary-500/10 uppercase shadow-inner">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-200 tracking-tight group-hover:text-primary-400 transition-colors">{{ $user->name }}</span>
                                            <span class="text-[10px] text-gray-500 italic">{{ $user->roles->first()?->name ?? 'Personal' }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                {{-- Celdas del calendario con Tooltips --}}
@foreach($daysInMonth as $day)
    @php 
        $attendance = $user->attendances->firstWhere('attendance_date', $day->format('Y-m-d'));
        
        // Preparamos el mensaje del tooltip según el estado
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
        {{-- Esta es la magia: Al hacer clic, llamamos a una función de Filament/Livewire --}}
        wire:click="openAttendanceModal('{{ $user->id }}', '{{ $day->format('Y-m-d') }}')"
        class="relative w-full h-9 rounded-lg transition-all duration-200 group/cell cursor-pointer"
    >
        @if($attendance)
            {{-- Presente --}}
            <div class="w-full h-full rounded-lg bg-green-500/80 border border-green-400/20 shadow-[0_0_10px_rgba(34,197,94,0.1)] group-hover/cell:scale-105 group-hover/cell:bg-green-400 transition-all"></div>
        @elseif($day->isPast() && !$day->isWeekend())
            {{-- Falta --}}
            <div class="w-full h-full rounded-lg bg-red-500/10 border border-red-500/20 opacity-60 group-hover/cell:opacity-100 group-hover/cell:bg-red-500/20 transition-all"></div>
        @else
            {{-- Vacío --}}
            <div class="w-full h-full rounded-lg border border-dashed border-white/5 opacity-10 group-hover/cell:opacity-30 group-hover/cell:border-primary-500 transition-all"></div>
        @endif
    </div>
</td>
@endforeach
                                {{-- Total Fijo (Horas Acumuladas) --}}
                                <td class="p-4 text-center sticky right-0 bg-[#0d0d0d] z-20 border-l border-white/10 group-hover:bg-[#121212] transition-colors">
                                    <div class="flex flex-col items-center">
                                        <span class="text-sm font-black text-gray-300">{{ $user->attendances->count() * 8 }}h</span>
                                        <span class="text-[9px] text-gray-600 font-medium italic">0m</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- PIE DE TABLA: Totales Diarios --}}
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
        </div> {{-- Fin del div p-6/p-10 interno --}}
    </div> {{-- Fin del div bg-[#0d0d0d] con rounded-2xl --}}

    {{-- LEYENDA DE ESTADOS: Guía visual profesional --}}
    <div class="mt-6 flex flex-wrap items-center gap-8 px-4">
        <div class="flex items-center gap-2.5">
            <div class="w-3.5 h-3.5 rounded-md bg-green-500/80 border border-green-400/20 shadow-[0_0_8px_rgba(34,197,94,0.2)]"></div>
            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Presente</span>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="w-3.5 h-3.5 rounded-md bg-red-500/20 border border-red-500/40"></div>
            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Ausencia</span>
        </div>
        <div class="flex items-center gap-2.5">
            <div class="w-3.5 h-3.5 rounded-md border border-dashed border-white/20"></div>
            <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Sin Registro / Futuro</span>
        </div>
    </div>
</div> {{-- Fin del contenedor mt-12 --}}

</x-filament-panels::page>