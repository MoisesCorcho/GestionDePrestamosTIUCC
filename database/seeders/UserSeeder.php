<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Juan Perez',
                'email' => 'juan.perez@ucc.edu.co',
                'password' => bcrypt('password'),
                'role' => 'AUXILIAR',
                'department' => 'OFICINA DE INFRAESTRUCTURA TECNOLÓGICA',
            ],
            [
                'name' => 'Maria Gomez',
                'email' => 'maria.gomez@ucc.edu.co',
                'password' => bcrypt('password'),
                'role' => 'ESTUDIANTE',
                'department' => 'PROGRAMA DE INGENIERÍA DE SISTEMAS',
            ],
        ];

        foreach ($users as $user) {
            $roleId = Role::where('nombre', $user['role'])->first()->id;
            $departmentId = Department::where('nombre', $user['department'])->first()->id;

            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'role_id' => $roleId,
                'department_id' => $departmentId,
                'country_id' => 48,
                'state_id' => 2898,
                'city_id' => 20558
            ]);
        }

        $roleId = Role::where('nombre', 'ADMINISTRADOR')->first()->id;
        $departmentId = Department::where('nombre', 'OFICINA DE INFRAESTRUCTURA TECNOLÓGICA')->first()->id;

        User::create([
            'name' => 'Moises Corcho',
            'email' => 'mcorchoperez@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $roleId,
            'department_id' => $departmentId,
            'country_id' => 48,
            'state_id' => 2898,
            'city_id' => 20558
        ]);
    }
}
