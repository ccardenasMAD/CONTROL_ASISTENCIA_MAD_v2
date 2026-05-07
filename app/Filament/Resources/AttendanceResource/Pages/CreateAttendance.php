<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    /**
     * Esta función se ejecuta justo antes de que el registro se guarde en la BD.
     * Aquí inyectamos la IP y calculamos el estado automáticamente.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Capturamos la IP de forma automática
        $data['ip_address'] = request()->ip();

      
        // Si el usuario marcó entrada pero no salida
        if (!empty($data['check_in']) && empty($data['check_out'])) {
            $data['status'] = 'present'; 
        } 
        // Si ya marcó ambas (entrada y salida)
        elseif (!empty($data['check_in']) && !empty($data['check_out'])) {
            $data['status'] = 'present';
        } 
        // Si no hay ninguna marca
        else {
            $data['status'] = 'absent';
        }

        // 3. Aseguramos que el origen sea móvil si viene del formulario
        if (empty($data['source'])) {
            $data['source'] = 'mobile';
        }

        return $data;
    }

    /**
     * Opcional: Redirigir a la lista después de crear
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}