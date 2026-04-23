<x-filament::page>
    <div class="max-w-2xl mx-auto space-y-6">

        {{-- Card info --}}
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

            @if(!$attendance)
                <x-filament::button 
                    wire:click="checkIn" 
                    size="xl"
                    class="px-10 py-4 text-base font-semibold
                           bg-black text-white 
                           hover:bg-gray-800 
                           dark:bg-white dark:text-black dark:hover:bg-gray-200
                           transition rounded-xl shadow-sm">
                    ⏰ Marcar Entrada
                </x-filament::button>

            @elseif(!$attendance->check_out)
                <x-filament::button 
                    wire:click="checkOut" 
                    size="xl"
                    class="px-10 py-4 text-base font-semibold
                           bg-gray-900 text-white 
                           hover:bg-black 
                           dark:bg-gray-100 dark:text-black dark:hover:bg-white
                           transition rounded-xl shadow-sm">
                    🚪 Marcar Salida
                </x-filament::button>

            @else
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        ✔ Asistencia completada
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Ya registraste entrada y salida hoy
                    </div>
                </div>
            @endif

        </div>

    </div>
</x-filament::page>