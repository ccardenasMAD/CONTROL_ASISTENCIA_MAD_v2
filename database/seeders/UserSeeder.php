<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'admin@assist.cl'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('12345678'),
            ]
        );
        $admin->syncRoles(['admin']);

        // RRHH
        $rrhh = User::firstOrCreate(
            ['email' => 'rrhh@assist.cl'],
            [
                'name' => 'RRHH',
                'password' => Hash::make('12345678'),
            ]
        );
        $rrhh->syncRoles(['rrhh']);

        // USUARIO NORMAL
        $user = User::firstOrCreate(
            ['email' => 'usuario@assist.cl'],
            [
                'name' => 'Usuario Normal',
                'password' => Hash::make('12345678'),
            ]
        );
        $user->syncRoles(['usuario']);
    }
}