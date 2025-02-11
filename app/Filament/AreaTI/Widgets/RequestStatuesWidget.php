<?php

namespace App\Filament\AreaTI\Widgets;

use App\Models\Request;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RequestStatuesWidget extends ChartWidget
{
    protected static ?string $heading = 'Numero de Peticiones por Tipo';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $acceptedRequest = Request::where('estado', 'aceptado')->count();
        $rejectedRequest = Request::where('estado', 'rechazado')->count();
        $completedRequest = Request::where('estado', 'completado')->count();
        $pendingRequest = Request::where('estado', 'pendiente')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Peticiones',
                    'data' => [$acceptedRequest, $rejectedRequest, $completedRequest, $pendingRequest],
                ],
            ],
            'labels' => ['Aceptado', 'Rechazado', 'Completado', 'Pendiente'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
