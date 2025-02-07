<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\NewRequest;
use Illuminate\Support\Facades\Notification;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Comenzando el envío de la notification...");

        $user = User::find(3);

        $this->info("Usuario encontrado: {$user->email}");

        $data = [
            'user' => [
                'name' => 'Juan Pérez',
            ],
            'products' => [
                [
                    'unit_nombre' => 'PARLANTE',
                    'unit_marca' => 'Bose',
                    'unit_modelo' => 'SoundLink Revolve+',
                    'unit_codigo_inventario' => 'INV-13-3',
                    'unit_serie' => 'SER-13-3',
                    'product_unit_id' => 243,
                ],
                [
                    'unit_nombre' => 'PARLANTE',
                    'unit_marca' => 'Bose',
                    'unit_modelo' => 'SoundLink Revolve+',
                    'unit_codigo_inventario' => 'INV-13-2',
                    'unit_serie' => 'SER-13-2',
                    'product_unit_id' => 242,
                ],
            ],
            'request_path' => '',
        ];

        Notification::send($user, new NewRequest($data));
    }
}
