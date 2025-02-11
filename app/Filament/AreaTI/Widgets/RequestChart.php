<?php

namespace App\Filament\AreaTI\Widgets;

use App\Models\Request;
use Carbon\Carbon;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class RequestChart extends ChartWidget
{
    use InteractsWithPageFilters;

    public ?string $filter = 'month';

    protected static ?string $heading = 'Peticiones Realizadas';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        // En caso de definir filtro general de dashboard
        // $generalStartDateFilter = $this->filters['startDate'];
        // $generalEndDateFilter = $this->filters['endDate'];

        // $data = Trend::model(Request::class)
        //     ->between(
        //         start: $generalStartDateFilter ? Carbon::parse($generalStartDateFilter) : now()->subMonth(),
        //         end: $generalEndDateFilter ? Carbon::parse($generalEndDateFilter) : now(),
        //     )
        //     ->perDay()
        //     ->count();

        // Definir los rangos de fechas según el filtro seleccionado
        $start = match ($this->filter) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subWeek(), // Valor por defecto (última semana)
        };

        $data = Trend::model(Request::class)
            ->between(
                start: $start ? $start : now()->subMonth(),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Request',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last week',
            'month' => 'Last month',
            'year' => 'This year',
        ];
    }
}
