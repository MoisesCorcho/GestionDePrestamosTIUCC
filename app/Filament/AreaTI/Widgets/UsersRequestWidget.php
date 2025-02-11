<?php

namespace App\Filament\AreaTI\Widgets;

use App\Models\Request;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UsersRequestWidget extends ChartWidget
{
    protected static ?string $heading = 'Usuarios Con Mas Peticiones';

    protected function getData(): array
    {
        $topUsers = Request::select('users.name', DB::raw('COUNT(user_id) as total_requests'))
            ->join('users', 'requests.user_id', '=', 'users.id')
            ->groupBy('requests.user_id', 'users.name')
            ->orderByDesc('total_requests')
            ->limit(5) // Obtener los 10 productos más pedidos
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Requests',
                    'data' => $topUsers->pluck('total_requests')->toArray(),
                ],
            ],
            'labels' => $topUsers->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
