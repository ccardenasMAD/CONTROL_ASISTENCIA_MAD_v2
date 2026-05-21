<x-filament-widgets::widget>
    <div class="p-6 rounded-2xl bg-slate-900/70 backdrop-blur ring-1 ring-slate-700/60 shadow-xl space-y-6">

        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                <x-heroicon-o-calendar class="w-5 h-5 text-white" />
            </div>
            <h3 class="text-lg font-bold text-white tracking-wide">Mi asistencia del mes</h3>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            {{-- Presentes --}}
            <div class="flex items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:border-emerald-400/30 transition">
                <x-heroicon-o-check-circle class="w-7 h-7 text-emerald-400 drop-shadow-[0_0_6px_rgba(16,185,129,0.4)]" />
                <div>
                    <p class="text-xs text-slate-400">Presentes</p>
                    <p class="text-2xl font-bold text-white">{{ $present }}</p>
                </div>
            </div>

            {{-- Ausentes --}}
            <div class="flex items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:border-red-400/30 transition">
                <x-heroicon-o-x-circle class="w-7 h-7 text-red-400 drop-shadow-[0_0_6px_rgba(248,113,113,0.4)]" />
                <div>
                    <p class="text-xs text-slate-400">Ausentes</p>
                    <p class="text-2xl font-bold text-white">{{ $absent }}</p>
                </div>
            </div>

            {{-- Atrasos --}}
            <div class="flex items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:border-yellow-400/30 transition">
                <x-heroicon-o-clock class="w-7 h-7 text-yellow-400 drop-shadow-[0_0_6px_rgba(250,204,21,0.4)]" />
                <div>
                    <p class="text-xs text-slate-400">Atrasos</p>
                    <p class="text-2xl font-bold text-white">{{ $late }}</p>
                </div>
            </div>

            {{-- Salidas anticipadas --}}
            <div class="flex items-center gap-3 p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:border-orange-400/30 transition">
                <x-heroicon-o-arrow-left-on-rectangle class="w-7 h-7 text-orange-400 drop-shadow-[0_0_6px_rgba(251,146,60,0.4)]" />
                <div>
                    <p class="text-xs text-slate-400">Salidas anticipadas</p>
                    <p class="text-2xl font-bold text-white">{{ $early_exit }}</p>
                </div>
            </div>

        </div>

        {{-- Horas totales --}}
        <div class="p-5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm flex items-center justify-between hover:border-blue-400/30 transition">
            <div>
                <p class="text-xs text-slate-400">Horas totales del mes</p>
                <p class="text-3xl font-bold text-white">
                    {{ intdiv($hours, 60) }}h {{ $hours % 60 }}m
                </p>
            </div>
            <x-heroicon-o-chart-bar class="w-12 h-12 text-blue-400 drop-shadow-[0_0_8px_rgba(96,165,250,0.4)]" />
        </div>

    </div>
</x-filament-widgets::widget>
