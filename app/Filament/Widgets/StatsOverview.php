<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Request;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\CarbonInterval;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected int|string|array $columns = 1; // Define que haya 2 columnas

    protected function getStats(): array
    {
        return [
            Stat::make(__('Total Requests Made'), $this->getAllRequests()),

            Stat::make(__('Average Resolution Time'), $this->avgResponseTime()),

            Stat::make(__('Time since last unresolved request'), $this->timeSinceLastPendingRequest())
        ];
    }

    protected function getAllRequests()
    {
        return Request::all()->count();
    }

    protected function timeSinceLastPendingRequest()
    {
        $lastPending = Request::where('estado', 'pendiente')->latest('created_at')->first();

        $lastPending ? $timeDifference = now()->diff($lastPending->created_at) : '';

        return $lastPending ? $timeDifference : 'No hay pendientes';
    }

    protected function avgResponseTime()
    {
        // Agrupamos por request_id y obtenemos las fechas de inicio y fin de cada solicitud
        $requests = \App\Models\RequestLog::query()
            ->whereIn('estado', ['pendiente', 'aceptado', 'rechazado'])
            ->groupBy('request_id')
            ->selectRaw('request_id, MIN(CASE WHEN estado = "pendiente" THEN created_at END) as created_at_pendiente, MIN(CASE WHEN estado = "aceptado" OR estado = "rechazado" THEN created_at END) as created_at_final')
            ->get();

        // Calculamos la diferencia de tiempo para cada solicitud y obtenemos el promedio
        $responseTimes = $requests->map(function ($request) {
            return Carbon::parse($request->created_at_pendiente)->diffInMinutes($request->created_at_final);
        });

        // Calculamos el promedio en minutos
        $averageMinutes = $responseTimes->avg();

        $formattedTime = CarbonInterval::minutes($averageMinutes)->cascade()->forHumans();

        return $formattedTime;
    }
}
