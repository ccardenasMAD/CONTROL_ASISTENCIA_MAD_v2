<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Filament\Resources\AttendanceResource\RelationManagers;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Asistencia';
    protected static ?string $navigationLabel = 'Asistencias';

    /**
     * Visibilidad en el menú
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() &&
            (
                auth()->user()->can('ver_asistencia') ||
                auth()->user()->can('ver_asistencia_mi_grupo')
            );
    }

    /**
     * Formulario (crear / editar)
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Usuario')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),

            Forms\Components\Select::make('group_id')
                ->label('Grupo')
                ->relationship('group', 'name')
                ->searchable()
                ->required(),

            Forms\Components\DatePicker::make('attendance_date')
                ->label('Fecha')
                ->required(),

            Forms\Components\DateTimePicker::make('check_in')
                ->label('Entrada'),

            Forms\Components\DateTimePicker::make('check_out')
                ->label('Salida'),

            Forms\Components\Select::make('source')
                ->label('Origen')
                ->options([
                    'manual' => 'Manual',
                    'system' => 'Sistema',
                    'mobile' => 'Móvil',
                    'biometric' => 'Biométrico',
                ])
                ->default('manual'),

            Forms\Components\Select::make('status')
                ->label('Estado')
                ->options([
                    'present' => 'Presente',
                    'absent' => 'Ausente',
                    'late' => 'Atraso',
                    'early_exit' => 'Salida anticipada',
                    'incomplete' => 'Incompleta',
                ])
                ->disabled(), // por ahora el sistema lo calculará luego
        ]);
    }

    /**
     * Tabla
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('attendance_date')
                    ->label('Fecha')
                    ->date(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable(),

                Tables\Columns\TextColumn::make('group.name')
                    ->label('Grupo'),

                Tables\Columns\TextColumn::make('check_in')
                    ->label('Entrada')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('check_out')
                    ->label('Salida')
                    ->dateTime(),

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
}
