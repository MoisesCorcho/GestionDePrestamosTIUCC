<?php

namespace App\Filament\Widgets;

use App\Models\Request;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RequestStatuesWidget extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Number of Requests by Type');
    }

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
            'labels' => [__('aceptado'), __('rechazado'), __('completado'), __('pendiente')],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
