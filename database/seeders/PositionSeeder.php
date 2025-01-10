<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
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
            'SUBDIRECTOR'
        ];

        foreach ($positions as $position) {
            Position::create(['nombre' => $position]);
        }
    }
}
