<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Lunes a Viernes (7:00 AM - 10:00 PM, Descanso 12:00 PM - 2:00 PM)
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        foreach ($daysOfWeek as $day) {
            DB::table('settings')->insert([
                'dia' => $day,
                'hora_apertura' => '07:00:00',
                'hora_cierre' => '22:00:00',
                'hora_solicitudes_apertura' => '07:00:00',
                'hora_solicitudes_cierre' => '21:30:00',
                'descanso_inicio' => '12:00:00',
                'descanso_fin' => '14:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Sábado (7:00 AM - 12:00 PM, Descanso 12:00 PM - 2:00 PM)
        DB::table('settings')->insert([
            'dia' => 'saturday',
            'hora_apertura' => '07:00:00',
            'hora_cierre' => '12:00:00',
            'hora_solicitudes_apertura' => '07:00:00',
            'hora_solicitudes_cierre' => '11:30:00',
            'descanso_inicio' => null,
            'descanso_fin' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
