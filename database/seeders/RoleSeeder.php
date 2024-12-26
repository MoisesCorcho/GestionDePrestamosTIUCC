<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $roles = [
            'ANALISTA',
            'APRENDIZ',
            'AUXILIAR',
            'COORDINADOR',
            'DIRECTOR',
            'ESTUDIANTE',
            'INSTRUCTOR',
            'JEFE',
            'PRACTICANTE',
            'PROFESOR CATEDRATICO',
            'PROFESOR CATEDRATICO ADMINISTRATIVO',
            'PROFESOR MEDIO TIEMPO',
            'PROFESOR TIEMPO COMPLETO',
            'SUBDIRECTOR',
            'ADMINISTRADOR'
        ];

        foreach ($roles as $role) {
            Role::create(['nombre' => $role]);
        }
    }
}
