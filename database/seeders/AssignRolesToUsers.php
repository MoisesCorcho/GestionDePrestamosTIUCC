<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AssignRolesToUsers extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('email', 'like', '%moises.corcho@campusucc.edu.co%')->first();
        $tiUser1 = User::where('email', 'like', '%juan.perez@ucc.edu.co%')->first();
        $tiUser2 = User::where('email', 'like', '%omar.doria@campussucc.edu.co%')->first();
        $tiUser3 = User::where('email', 'like', '%pedro.pinto@campussucc.edu.co%')->first();
        $tiUser4 = User::where('email', 'like', '%alvaro.hernandez@campussucc.edu.co%')->first();
        $tiUser5 = User::where('email', 'like', '%roberto.palma@campussucc.edu.co%')->first();

        $adminUser->assignRole('panel_user');
        $tiUser1->assignRole('area_ti');

        $this->command->info('Asignacion de roles correcta.');
    }
}
