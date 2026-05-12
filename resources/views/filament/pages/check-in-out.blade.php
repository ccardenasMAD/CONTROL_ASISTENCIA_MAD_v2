<x-filament::page>
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Estado del día --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 
                    bg-white dark:bg-gray-900 
                    shadow-sm p-6 flex items-center justify-between">
            <div>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Hoy
                </div>
                <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    {{ $dayStatusLabel ?? 'Sin registro' }}
                </div>

                <div class="mt-2 flex flex-wrap gap-3 text-sm text-gray-600 dark:text-gray-300">
                    @if($checkInTime)
                        <span>Entrada: <span class="font-semibold">{{ $checkInTime }}</span></span>
                    @endif

                    @if($checkOutTime)
                        <span>Salida: <span class="font-semibold">{{ $checkOutTime }}</span></span>
                    @endif

                    @if($workedDuration)
                        <span>Trabajado: <span class="font-semibold">{{ $workedDuration }}</span></span>
                    @endif
                </div>
            </div>

            <div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $dayStatusColor }}">
                    {{ $dayStatusLabel ?? 'Sin estado' }}
                </span>
            </div>
        </div>

        {{-- Información de turno --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 
                    bg-white dark:bg-gray-900 
                    shadow-sm p-6 space-y-3">

            <div class="text-sm text-gray-500 dark:text-gray-400">
                Información de turno
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">

                <div>
                    <div class="text-xs text-gray-400">Grupo</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ $groupName ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-400">Turno</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ $shiftName ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-gray-400">Horario</div>
                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ $shiftSchedule ?? '—' }}
                    </div>
                </div>

            </div>
        </div>

        {{-- Acción principal --}}
        <div class="flex justify-center">

            @if($dayStatus === 'vacation')
                <div class="text-center">
                    <div class="text-lg font-semibold text-pink-600 dark:text-pink-400">
                        Día marcado como vacaciones
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        No puedes marcar asistencia en un día de vacaciones.
                    </div>
                </div>

            @elseif(!$attendance)
                <x-filament::button 
                    wire:click="checkIn" 
                    size="xl"
                    class="px-10 py-4 text-base font-semibold
                           bg-emerald-600 text-white 
                           hover:bg-emerald-700 
                           dark:bg-emerald-500 dark:hover:bg-emerald-600
                           transition rounded-xl shadow-sm">
                    ⏰ Marcar entrada
                </x-filament::button>

            @elseif(!$attendance->check_out)
                <x-filament::button 
                    wire:click="checkOut" 
                    size="xl"
                    class="px-10 py-4 text-base font-semibold
                           bg-slate-800 text-white 
                           hover:bg-black 
                           dark:bg-slate-200 dark:text-black dark:hover:bg-white
                           transition rounded-xl shadow-sm">
                    🚪 Marcar salida
                </x-filament::button>

            @else
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        ✔ Asistencia completada
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Ya registraste entrada y salida hoy.
                    </div>
                </div>
            @endif

        </div>

    </div>
</x-filament::page>
