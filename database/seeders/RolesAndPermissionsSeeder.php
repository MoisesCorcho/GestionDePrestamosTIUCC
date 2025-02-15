<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ejecutar el comando php artisan shield:generate --all
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin']);

        $adminUser = User::where('email', 'like', '%mcorchoperez@gmail.com%')->first();

        // Ejecutar el comando php artisan shield:super-admin para crear el rol de super_admin
        Artisan::call('shield:super-admin', ['--user' => $adminUser->id]);

        // Crear los roles
        $panelUserRole = Role::firstOrCreate(['name' => 'panel_user']);
        $areaTiRole = Role::firstOrCreate(['name' => 'area_ti']);

        // Obtener permisos que contienen "request" en su nombre
        $requestPermissions = Permission::where('name', 'like', '%request%')->get();

        // Asignar permisos a los roles
        $panelUserRole->syncPermissions($requestPermissions);
        $areaTiRole->syncPermissions($requestPermissions);

        $this->command->info('Roles y permisos creados correctamente.');
    }
}
