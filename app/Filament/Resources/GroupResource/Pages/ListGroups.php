<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGroups extends ListRecords
{
    protected static string $resource = GroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
        {
            $user = auth()->user();

            // Admin: ve todo
            if ($user->can('ver_grupos')) {
                return parent::getTableQuery();
            }

            // Jefe: solo sus grupos
            return parent::getTableQuery()
                ->whereHas('leaders', fn ($q) =>
                    $q->where('users.id', $user->id)
                );
        }
}

