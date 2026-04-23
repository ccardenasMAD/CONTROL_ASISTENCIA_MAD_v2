<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\User;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios base
        $admin   = User::where('email', 'admin@assist.cl')->first();
        $rrhh    = User::where('email', 'rrhh@assist.cl')->first();
        $usuario = User::where('email', 'usuario@assist.cl')->first();

        // --- Grupo 1 ---
        $grupoOperaciones = Group::create([
            'name' => 'Operaciones',
            'description' => 'Equipo de operaciones generales',
            'is_active' => true,
        ]);

        $grupoOperaciones->users()->sync([
            $rrhh->id,
            $usuario->id,
        ]);

        $grupoOperaciones->leaders()->sync([
            $rrhh->id, // RRHH actúa como jefe de este grupo
        ]);

        // --- Grupo 2 ---
        $grupoTI = Group::create([
            'name' => 'Tecnología',
            'description' => 'Equipo de desarrollo y soporte TI',
            'is_active' => true,
        ]);

        $grupoTI->users()->sync([
            $usuario->id,
        ]);

        $grupoTI->leaders()->sync([
            $admin->id, // Admin es jefe de este grupo
        ]);

        // --- Grupo 3 ---
        $grupoRRHH = Group::create([
            'name' => 'Recursos Humanos',
            'description' => 'Área de RRHH',
            'is_active' => true,
        ]);

        $grupoRRHH->users()->sync([
            $rrhh->id,
        ]);

        $grupoRRHH->leaders()->sync([
            $rrhh->id,
        ]);
    }
}
