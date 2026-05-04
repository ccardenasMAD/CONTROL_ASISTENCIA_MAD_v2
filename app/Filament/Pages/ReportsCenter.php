<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Exports\ReportsCenterExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportsCenter extends Page implements Forms\Contracts\HasForms, HasTable
{
    use Forms\Concerns\InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.reports-center';

    /* =====================
     * ESTADO DEL FORMULARIO
     * ===================== */
    public ?string $startDate = null;
    public ?string $endDate   = null;
    public ?int $userId       = null;
    public ?int $groupId      = null;
    public string $orderBy    = 'group';

    /* =====================
     * ESTADO DEL REPORTE
     * ===================== */
    public bool $showResults = false;

    /* =====================
     * FORMULARIO
     * ===================== */
    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Fecha inicio'),

            DatePicker::make('endDate')
                ->label('Fecha fin'),

            Select::make('userId')
                ->label('Usuario')
                ->options(
                    User::orderBy('name')->pluck('name', 'id')
                )
                ->searchable()
                ->placeholder('Todos los usuarios'),

            Select::make('groupId')
                ->label('Grupo')
                ->options(
                    Group::orderBy('name')->pluck('name', 'id')
                )
                ->searchable()
                ->placeholder('Todos los grupos'),

            Select::make('orderBy')
                ->label('Ordenar por')
                ->options([
                    'group' => 'Grupo',
                    'user'  => 'Usuario',
                    'date'  => 'Fecha',
                ])
                ->default('group'),
        ];
    }

    /* =====================
     * ACCIONES DEL HEADER
     * ===================== */
    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('today')
                    ->label('Hoy')
                    ->action(fn () => $this->setRangeAndGenerate(
                        Carbon::today(),
                        Carbon::today()
                    )),

                Action::make('last7')
                    ->label('Últimos 7 días')
                    ->action(fn () => $this->setRangeAndGenerate(
                        Carbon::today()->subDays(6),
                        Carbon::today()
                    )),

                Action::make('last30')
                    ->label('Últimos 30 días')
                    ->action(fn () => $this->setRangeAndGenerate(
                        Carbon::today()->subDays(29),
                        Carbon::today()
                    )),

                Action::make('thisMonth')
                    ->label('Este mes')
                    ->action(fn () => $this->setRangeAndGenerate(
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    )),

                Action::make('lastMonth')
                    ->label('Mes anterior')
                    ->action(fn () => $this->setRangeAndGenerate(
                        Carbon::now()->subMonth()->startOfMonth(),
                        Carbon::now()->subMonth()->endOfMonth()
                    )),
            ])
                ->label('Rangos rápidos')
                ->icon('heroicon-o-calendar')
                ->tooltip('Selecciona un rango predefinido'),

            Action::make('generate')
                ->label('Generar reporte')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->action('generateReport'),

            Action::make('export_excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn () => $this->showResults)
                ->action('downloadExcel'),
            
            Action::make('export_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn () => $this->showResults)
                ->url(fn () => route('reports.pdf', [
                    'start'     => $this->startDate,
                    'end'       => $this->endDate,
                    'user_id'   => $this->userId,
                    'group_id'  => $this->groupId,
                    'order_by'  => $this->orderBy,
                ]))
                ->openUrlInNewTab(),
        ];
    }

    /* =====================
     * GENERAR REPORTE
     * ===================== */
    public function generateReport(): void
    {
        // ✅ Si no hay fechas → usar HOY
        if (! $this->startDate || ! $this->endDate) {
            $this->startDate = Carbon::today()->toDateString();
            $this->endDate   = Carbon::today()->toDateString();
        }

        $this->showResults = true;
    }

    /* =====================
     * CONSTRUCCIÓN DE QUERY
     * (FUENTE ÚNICA DE VERDAD)
     * ===================== */
    protected function buildQuery(): Builder
    {
        $query = Attendance::query()
            ->select('attendances.*')
            ->with(['user', 'group'])
            ->whereBetween('date', [
                $this->startDate,
                $this->endDate,
            ]);

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->groupId) {
            $query->where('group_id', $this->groupId);
        }

        match ($this->orderBy) {
            'group' => $query
                ->join('groups', 'groups.id', '=', 'attendances.group_id')
                ->orderBy('groups.name'),

            'user'  => $query
                ->join('users', 'users.id', '=', 'attendances.user_id')
                ->orderBy('users.name'),

            'date'  => $query->orderBy('date'),
        };

        return $query;
    }

    /* =====================
     * TABLA
     * ===================== */
    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(fn () => $this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario'),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('Grupo'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Evento')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in' => 'Entrada',
                        'out' => 'Salida',
                        default => $state,
                    })
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('time')
                    ->label('Hora')
                    ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : '—'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'present'     => 'Presente',
                        'late'        => 'Atraso',
                        'early_exit'  => 'Salida anticipada',
                        'incomplete'  => 'Incompleta',
                        'absent'      => 'Ausente',
                        default       => '—',
                    })
                    ->colors([
                        'success' => 'present',
                        'warning' => 'late',
                        'primary' => 'early_exit',
                        'gray'    => 'incomplete',
                        'danger'  => 'absent',
                    ]),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        if (! $this->showResults) {
            return Attendance::query()->whereRaw('1 = 0');
        }

        return $this->buildQuery();
    }

    /* =====================
     * EXPORTAR EXCEL
     * ===================== */
    public function downloadExcel()
    {
        if (! $this->showResults) {
            Notification::make()
                ->title('Primero genera un reporte')
                ->warning()
                ->send();
            return;
        }

        return Excel::download(
            new ReportsCenterExport(
                $this->buildQuery()->get()
            ),
            'reporte-asistencia.xlsx'
        );
    }

    /* =====================
     * HELPERS
     * ===================== */
    private function setRange(Carbon $start, Carbon $end): void
    {
        $this->startDate = $start->toDateString();
        $this->endDate   = $end->toDateString();
    }

    private function setRangeAndGenerate(Carbon $start, Carbon $end): void
    {
        $this->setRange($start, $end);
        $this->generateReport();
    }

    public static function canAccess(): bool
    {
        return auth()->check()
            && auth()->user()->can('ver_asistencia');
    }
}
