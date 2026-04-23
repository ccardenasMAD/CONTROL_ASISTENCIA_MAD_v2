<?php

namespace App\Filament\Resources\ShiftResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShiftGroup extends RelationManager
{
    /**
     * Relación en el modelo Shift
     */
    protected static string $relationship = 'shiftGroups';

    protected static ?string $title = 'Grupos asignados';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('group_id')
                ->label('Grupo')
                ->relationship('group', 'name')
                ->required(),

            Forms\Components\DatePicker::make('start_date')
                ->label('Desde')
                ->required(),

            Forms\Components\DatePicker::make('end_date')
                ->label('Hasta'),

            Forms\Components\Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Grupo'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Desde'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Hasta'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
