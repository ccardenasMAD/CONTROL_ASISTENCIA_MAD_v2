<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Filament\Resources\ShiftResource\RelationManagers;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;



class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Planificacion';
    protected static ?string $navigationLabel = 'Turnos';

    /**
     * Visibilidad en el menú
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('ver_turnos');
    }

    /**
     * Formulario de crear / editar turno
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Información del turno')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del turno')
                            ->required()
                            ->maxLength(255),

                        Select::make('calculation_type')
                            ->label('Tipo de cálculo')
                            ->options([
                                'shift' => 'Por turno',
                                'daily' => 'Diario',
                            ])
                            ->required(),
                    ]),

                Section::make('Horario')
                    ->schema([
                        TimePicker::make('start_time')
                            ->label('Hora de inicio')
                            ->required(),

                        TimePicker::make('end_time')
                            ->label('Hora de término')
                            ->required(),

                        Toggle::make('is_night_shift')
                            ->label('Turno nocturno')
                            ->helperText('Actívalo si el turno cruza de día'),
                    ])
                    ->columns(3),

                Section::make('Reglas de asistencia')
                    ->schema([
                        TextInput::make('grace_minutes')
                            ->label('Minutos de tolerancia')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),

                        TextInput::make('min_work_hours')
                            ->label('Horas mínimas de trabajo')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Horas mínimas para que la asistencia sea válida'),
                    ])
                    ->columns(2),

                Section::make('Estado')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Turno activo')
                            ->default(true),
                    ]),
            ]);
    }

    /**
     * Tabla de turnos
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Turno')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_time')
                    ->label('Inicio'),

                TextColumn::make('end_time')
                    ->label('Término'),

                TextColumn::make('grace_minutes')
                    ->label('Tolerancia (min)'),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('editar_turnos')),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('eliminar_turnos')),
            ]);
    }

    /**
     * Páginas del Resource
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ShiftGroup::class,
        ];
    }
}
