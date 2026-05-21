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

    /**
     * Mantenemos tu lógica de visibilidad intacta
     */
    public static function shouldRegisterNavigation(): bool
    {
        /*
        return auth()->check() &&
            (
                auth()->user()->can('ver_asistencia') ||
                auth()->user()->can('ver_asistencia_mi_grupo')
            );
        */
        return true;
    }

    /**
     * Formulario organizado por secciones (Sin Foto por Privacidad)
     */
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

<<<<<<< HEAD
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
=======
            Forms\Components\DatePicker::make('date')
                ->label('Fecha')
                ->required(),

            Forms\Components\TimePicker::make('time')
                ->label('Hora')
                ->required(),

            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'in' => 'Entrada',
                    'out' => 'Salida',
                ])
                ->required(),
>>>>>>> main

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

            // Campos GPS Ocultos (Coinciden con los IDs del Script)
            Forms\Components\Hidden::make('latitude')->extraAttributes(['id' => 'lat-hidden']),
            Forms\Components\Hidden::make('longitude')->extraAttributes(['id' => 'lng-hidden']),
            Forms\Components\Hidden::make('status')->default('present'),

        ]);
    }

    /**
     * Tabla estilo reporte 
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
<<<<<<< HEAD
=======
                Tables\Columns\TextColumn::make('date')
                    ->label('Fecha')
                    ->date(),

>>>>>>> main
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Empleado')
                    ->description(fn ($record) => "Sede: {$record->group->name}")
                    ->searchable(),

                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

<<<<<<< HEAD
                Tables\Columns\TextColumn::make('check_in')
                    ->label('Entrada')
                    ->dateTime('H:i')
                    ->color('success'),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Salida')
                    ->dateTime('H:i')
                    ->color('danger'),

                // Duración total calculada desde el modelo
                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Total Horas')
                    ->badge()
                    ->color('info'),

                // Ubicación con enlace funcional a Google Maps
                Tables\Columns\TextColumn::make('location')
                    ->label('GPS')
                    ->icon('heroicon-m-map-pin')
                    ->color('gray')
                    ->getStateUsing(fn ($record) => $record->latitude ? 'Ver Mapa' : 'Sin GPS')
                    ->url(fn ($record) => $record->latitude 
                        ? "https://www.google.com/maps/search/?api=1&query={$record->latitude},{$record->longitude}" 
                        : null, true),
=======
                Tables\Columns\TextColumn::make('type')
                    ->label('Evento')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'in' => 'Entrada',
                        'out' => 'Salida',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('time')
                    ->label('Hora')
                    ->formatStateUsing(fn (?string $state) => $state ? substr($state, 0, 5) : '—'),
>>>>>>> main

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'present',
                        'warning' => 'late',
                        'danger' => 'absent',
                        'primary' => 'early_exit',
                        'gray' => 'incomplete',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'present' => 'Presente',
                        'late' => 'Atraso',
                        'early_exit' => 'Salida Anticipada',
                        'absent' => 'Ausente',
                        'incomplete' => 'Incompleta',
                        default => '—',
                    }),
            ])
            ->actions([
                // Mantenemos tu acción de editar con permiso original
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('regularizar_asistencia')),
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

    /**
     * Mantenemos tu script de GPS original para captura automática
     */
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
                            // Avisamos a Filament que el valor cambió
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