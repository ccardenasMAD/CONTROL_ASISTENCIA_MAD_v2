<x-filament-widgets::widget>
    <div class="p-5 bg-gray-800 rounded-xl shadow-lg text-white">
        <h3 class="text-sm uppercase tracking-wide text-gray-400 mb-3">Mi asistencia del mes</h3>

        <ul class="space-y-2 text-gray-300">
            <li>Presentes: <span class="font-bold">{{ $present }}</span></li>
            <li>Ausentes: <span class="font-bold">{{ $absent }}</span></li>
            <li>Atrasos: <span class="font-bold">{{ $late }}</span></li>
            <li>Salidas anticipadas: <span class="font-bold">{{ $early_exit }}</span></li>
            <li>Horas totales:
                <span class="font-bold">
                    {{ intdiv($hours, 60) }}h {{ $hours % 60 }}m
                </span>
            </li>
        </ul>
    </div>
</x-filament-widgets::widget>
