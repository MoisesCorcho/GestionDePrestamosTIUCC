<?php

namespace Database\Seeders;

use App\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Product;
use App\Models\Department;
use App\Models\ProductUnit;
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
        $this->call(RoleSeeder::class);
        $this->call(DepartmentSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(UserSeeder::class);
    }
}
