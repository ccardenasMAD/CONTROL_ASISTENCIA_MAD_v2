<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear cache de permisos
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',

            'ver_roles',
            'crear_roles',
            'editar_roles',
            'eliminar_roles',

            'ver_permisos',
            'crear_permisos',
            'editar_permisos',
            'eliminar_permisos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $rrhh = Role::firstOrCreate(['name' => 'rrhh']);
        $rrhh->syncPermissions([
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',    
        ]);

        $usuario = Role::firstOrCreate(['name' => 'usuario']);
        // Sin permisos por ahora
        $usuario->syncPermissions([]);
    }
}