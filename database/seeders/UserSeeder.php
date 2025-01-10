<?php

namespace Database\Seeders;

use App\Models\Position;
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
                'position' => 'AUXILIAR',
                'department' => 'OFICINA DE INFRAESTRUCTURA TECNOLÓGICA',
            ],
            [
                'name' => 'Moises Estudiante',
                'email' => 'moises.corcho@campusucc.edu.co',
                'password' => bcrypt('password'),
                'position' => 'ESTUDIANTE',
                'department' => 'PROGRAMA DE INGENIERÍA DE SISTEMAS',
            ],
        ];

        foreach ($users as $user) {
            $positionId = Position::where('nombre', $user['position'])->first()->id;
            $departmentId = Department::where('nombre', $user['department'])->first()->id;

            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $user['password'],
                'position_id' => $positionId,
                'department_id' => $departmentId,
                'country_id' => 48,
                'state_id' => 2898,
                'city_id' => 20558
            ]);
        }

        $positionId = Position::where('nombre', 'JEFE')->first()->id;
        $departmentId = Department::where('nombre', 'OFICINA DE INFRAESTRUCTURA TECNOLÓGICA')->first()->id;

        User::create([
            'name' => 'Moises Corcho',
            'email' => 'mcorchoperez@gmail.com',
            'password' => bcrypt('password'),
            'position_id' => $positionId,
            'department_id' => $departmentId,
            'country_id' => 48,
            'state_id' => 2898,
            'city_id' => 20558
        ]);
    }
}
