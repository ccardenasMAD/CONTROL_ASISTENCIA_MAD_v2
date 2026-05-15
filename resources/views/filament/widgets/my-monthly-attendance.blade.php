<x-filament-widgets::widget>
    <div class="p-6 rounded-xl bg-gray-900/40 backdrop-blur border border-white/10 space-y-6">

        <h3 class="text-lg font-semibold text-white tracking-wide">
            Mi asistencia del mes
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            {{-- Presentes --}}
            <div class="flex items-center gap-3 p-4 rounded-lg bg-white/5 border border-white/10">
                <x-heroicon-o-check-circle class="w-6 h-6 text-green-400" />
                <div>
                    <p class="text-xs text-gray-400">Presentes</p>
                    <p class="text-xl font-bold text-white">{{ $present }}</p>
                </div>
            </div>

            {{-- Ausentes --}}
            <div class="flex items-center gap-3 p-4 rounded-lg bg-white/5 border border-white/10">
                <x-heroicon-o-x-circle class="w-6 h-6 text-red-400" />
                <div>
                    <p class="text-xs text-gray-400">Ausentes</p>
                    <p class="text-xl font-bold text-white">{{ $absent }}</p>
                </div>
            </div>

            {{-- Atrasos --}}
            <div class="flex items-center gap-3 p-4 rounded-lg bg-white/5 border border-white/10">
                <x-heroicon-o-clock class="w-6 h-6 text-yellow-400" />
                <div>
                    <p class="text-xs text-gray-400">Atrasos</p>
                    <p class="text-xl font-bold text-white">{{ $late }}</p>
                </div>
            </div>

            {{-- Salidas anticipadas --}}
            <div class="flex items-center gap-3 p-4 rounded-lg bg-white/5 border border-white/10">
                <x-heroicon-o-arrow-left-on-rectangle class="w-6 h-6 text-orange-400" />
                <div>
                    <p class="text-xs text-gray-400">Salidas anticipadas</p>
                    <p class="text-xl font-bold text-white">{{ $early_exit }}</p>
                </div>
            </div>

        </div>

        {{-- Horas totales --}}
        <div class="p-4 rounded-lg bg-white/5 border border-white/10 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400">Horas totales del mes</p>
                <p class="text-2xl font-bold text-white">
                    {{ intdiv($hours, 60) }}h {{ $hours % 60 }}m
                </p>
            </div>
            <x-heroicon-o-chart-bar class="w-10 h-10 text-blue-400" />
        </div>

    </div>
</x-filament-widgets::widget>
