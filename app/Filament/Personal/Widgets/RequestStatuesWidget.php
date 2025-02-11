<?php

namespace App\Filament\Personal\Widgets;

use App\Models\Request;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class RequestStatuesWidget extends ChartWidget
{
    protected static ?string $heading = 'Numero de Peticiones por Tipo';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $acceptedRequest = Request::where('estado', 'aceptado')->where('requests.user_id', '=', Auth::user()->id)->count();
        $rejectedRequest = Request::where('estado', 'rechazado')->where('requests.user_id', '=', Auth::user()->id)->count();
        $completedRequest = Request::where('estado', 'completado')->where('requests.user_id', '=', Auth::user()->id)->count();
        $pendingRequest = Request::where('estado', 'pendiente')->where('requests.user_id', '=', Auth::user()->id)->count();

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
