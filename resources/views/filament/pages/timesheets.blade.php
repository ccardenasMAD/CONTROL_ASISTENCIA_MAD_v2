<x-filament-panels::page>
    <div class="space-y-8">
        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">
                {{ \Carbon\Carbon::create($year, $month)->translatedFormat('F Y') }}
            </h2>

            <div class="flex items-center gap-3">
                <x-filament::button wire:click="changeMonth(-1)" color="gray" icon="heroicon-o-chevron-left">
                    Mes anterior
                </x-filament::button>

                <x-filament::button wire:click="changeMonth(1)" color="gray" icon="heroicon-o-chevron-right" icon-position="after">
                    Mes siguiente
                </x-filament::button>
            </div>
        </div>

        {{-- FILTROS --}}
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-64">
                <x-filament::input.wrapper>
                    <x-filament::input wire:model.live.debounce.500ms="search" placeholder="Buscar empleado..." />
                </x-filament::input.wrapper>
            </div>

            <div class="w-64">
                <x-filament::input.select wire:model.live="groupId">
                    <option value="">Todos los grupos</option>
                    @foreach(App\Models\Group::all() as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </x-filament::input.select>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Empleado</th>
                        @foreach($days as $day => $date)
                            <th class="px-2 py-3 text-center w-10 font-semibold">{{ $day }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-right font-semibold">Total</th>
                    </tr>
                </thead>

                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($usersData as $user)
                        <tr wire:key="user-{{ $user['id'] }}">
                            <td class="px-4 py-3 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <span class="text-xs">{{ substr($user['name'], 0, 1) }}</span>
                                </div>
                                <span class="font-medium">{{ $user['name'] }}</span>
                            </td>

                            @foreach($days as $day => $date)
                                @php $cell = $user['calendar'][$date]; @endphp
                                <td class="px-2 py-2 text-center">
                                    <button 
                                        type="button"
                                        wire:click="openAttendanceModal({{ $user['id'] }}, '{{ $date }}')"
                                        x-on:click="$dispatch('open-modal', { id: 'attendanceModal' })"
                                        class="w-6 h-6 mx-auto rounded transition hover:scale-110 {{ $cell['color'] }}"
                                        title="{{ $cell['status'] }}"
                                    ></button>
                                </td>
                            @endforeach

                            <td class="px-4 py-3 text-right font-semibold">
                                {{ intdiv($user['totalMinutes'], 60) }}h {{ $user['totalMinutes'] % 60 }}m
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- MODAL --}}
        <x-filament::modal id="attendanceModal" width="md">
            <x-slot name="heading">
                Detalles de Asistencia
            </x-slot>

            @if($modalData)
                <div class="space-y-3 p-2">
                    <div class="text-lg font-bold border-b pb-2">{{ $modalData['user'] }}</div>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <span class="text-gray-500">Fecha:</span>
                        <span class="font-medium">
                            {{ \Carbon\Carbon::parse($modalData['date'])->translatedFormat('d F Y') }}
                        </span>

                        <span class="text-gray-500">Estado:</span>
                        <span class="font-medium uppercase">{{ $modalData['status'] }}</span>

                        <span class="text-gray-500">Entrada:</span>
                        <span class="font-medium">{{ $modalData['check_in'] ?? '—' }}</span>

                        <span class="text-gray-500">Salida:</span>
                        <span class="font-medium">{{ $modalData['check_out'] ?? '—' }}</span>

                        <span class="text-gray-500 text-base font-bold mt-2">Total:</span>
                        <span class="text-base font-bold mt-2">
                            {{ intdiv($modalData['worked'], 60) }}h {{ $modalData['worked'] % 60 }}m
                        </span>
                    </div>
                </div>
            @endif

            <x-slot name="footer">
                <x-filament::button 
                    color="gray" 
                    x-on:click="$dispatch('close-modal', { id: 'attendanceModal' })"
                    class="w-full"
                >
                    Cerrar
                </x-filament::button>
            </x-slot>
        </x-filament::modal>

    </div>
</x-filament-panels::page>
