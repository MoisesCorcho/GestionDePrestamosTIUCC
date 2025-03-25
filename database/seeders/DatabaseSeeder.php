<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\WorldTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(WorldTableSeeder::class);
        $this->call(PositionSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(AssignRolesToUsers::class);
        $this->call(RequestsSeeder::class);
        $this->call(SettingsSeeder::class);
    }
}
