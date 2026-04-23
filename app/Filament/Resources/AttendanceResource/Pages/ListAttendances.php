<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        // Admin / RRHH
        if ($user->can('ver_asistencia')) {
            return parent::getTableQuery();
        }

        // Jefe: solo los grupos que lidera
        return parent::getTableQuery()
            ->whereIn(
                'group_id',
                $user->leadingGroups->pluck('id')
            );
    }
}
