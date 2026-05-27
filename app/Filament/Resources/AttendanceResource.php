<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static ?string $navigationLabel = 'Asistencias';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Sección 1: Quién y Dónde
            Forms\Components\Section::make('Información de Turno')
                ->description('Identificación del empleado y lugar de trabajo')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Empleado')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('group_id')
                        ->label('Grupo/Sede')
                        ->relationship('group', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\DatePicker::make('attendance_date')
                        ->label('Fecha')
                        ->default(now())
                        ->required(),
                ])->columns(3),

            // Sección 2: Registro de Tiempo y Biometría
            Forms\Components\Section::make('Registro de Tiempos')
                ->schema([
                    Forms\Components\DateTimePicker::make('check_in')
                        ->label('Entrada')
                        ->default(now()),

                    Forms\Components\DateTimePicker::make('check_out')
                        ->label('Salida'),

                    Forms\Components\DateTimePicker::make('break_start')
                        ->label('Inicio de Break'),

                    Forms\Components\DateTimePicker::make('break_end')
                        ->label('Fin de Break'),

                    Forms\Components\Select::make('source')
                        ->label('Origen del Registro')
                        ->options([
                            'manual' => 'Manual',
                            'system' => 'Sistema',
                            'mobile' => 'Móvil',
                            'biometric' => 'Biométrico',
                        ])
                        ->default('biometric'),

                    Forms\Components\Placeholder::make('biometric_notice')
                        ->label('Estado Biométrico')
                        ->content('Validación mediante Face-ID activa (No se guardan imágenes)'),
                ])->columns(2),

            // Campos GPS Ocultos corregidos para coincidir con tu base de datos y script
            Forms\Components\Hidden::make('check_in_lat')->extraAttributes(['id' => 'lat-hidden']),
            Forms\Components\Hidden::make('check_in_lng')->extraAttributes(['id' => 'lng-hidden']),
            Forms\Components\Hidden::make('status'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Empleado')
                    ->description(fn ($record) => "Sede: " . ($record->group?->name ?? 'Sin sede'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Entrada')
                    ->dateTime('H:i')
                    ->color('success'),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Salida')
                    ->dateTime('H:i')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Total Horas')
                    ->badge()
                    ->color('info'),

                // Geolocalización mapeada de manera correcta con enlace dinámico
                Tables\Columns\TextColumn::make('location')
                    ->label('GPS')
                    ->icon('heroicon-m-map-pin')
                    ->color('gray')
                    ->getStateUsing(fn ($record) => $record->check_in_lat ? 'Ver Mapa' : 'Sin GPS')
                    ->url(fn ($record) => $record->check_in_lat 
                        ? "https://www.google.com/maps/search/?api=1&query={$record->check_in_lat},{$record->check_in_lng}" 
                        : null, true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    // Lee dinámicamente tu método getStatus() del modelo para evitar incongruencias
                    ->getStateUsing(fn ($record) => $record->getStatus())
                    ->colors([
                        'success' => 'present',
                        'warning' => 'late',
                        'danger' => 'absent',
                        'primary' => 'early_exit',
                        'gray' => 'incomplete',
                        'pink' => 'vacation',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'present' => 'Presente',
                        'late' => 'Atraso',
                        'early_exit' => 'Salida Anticipada',
                        'absent' => 'Ausente',
                        'incomplete' => 'Incompleta',
                        'vacation' => 'Vacaciones',
                        default => '—',
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->can('regularizar_asistencia')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if (request()->routeIs('filament.admin.resources.attendances.create')) {
            echo "<script>
                navigator.geolocation.getCurrentPosition(function(position) {
                    setTimeout(function() {
                        var lat = document.getElementById('lat-hidden');
                        var lng = document.getElementById('lng-hidden');
                        if (lat && lng) {
                            lat.value = position.coords.latitude;
                            lng.value = position.coords.longitude;
                            lat.dispatchEvent(new Event('input'));
                            lng.dispatchEvent(new Event('input'));
                        }
                    }, 1000);
                });
            </script>";
        }
        return parent::getEloquentQuery();
    }
}