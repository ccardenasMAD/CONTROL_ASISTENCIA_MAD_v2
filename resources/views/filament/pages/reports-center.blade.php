<x-filament::page>
    <h2 class="text-xl font-bold mb-4">
        Centro de Reportes
    </h2>

    {{ $this->form }}

    @if ($showResults)
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @endif
</x-filament::page>
