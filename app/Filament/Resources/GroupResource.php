<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Planificacion';
    protected static ?string $navigationLabel = 'Grupos';

    /**
     * Control de visibilidad en el menú
     */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && (
            auth()->user()->can('ver_grupos') ||
            auth()->user()->can('ver_mi_grupo')
        );
    }

    /**
     * Formulario de crear / editar grupo
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre del grupo')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpanFull(),

                Select::make('users')
                    ->label('Miembros del grupo')
                    ->relationship('users', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->can('editar_grupos')),

                Select::make('leaders')
                    ->label('Jefes de grupo')
                    ->relationship('leaders', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->visible(fn () => auth()->user()->can('editar_grupos')),
            ]);
    }

    /**
     * Tabla de grupos
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Grupo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Miembros')
                    ->counts('users'),

                TextColumn::make('leaders.name')
                    ->label('Jefes')
                    ->badge(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()->can('editar_grupos')),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->can('eliminar_grupos')),
            ]);
    }

    /**
     * Páginas del Resource
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
