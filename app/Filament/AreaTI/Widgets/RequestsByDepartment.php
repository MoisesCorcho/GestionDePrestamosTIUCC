<?php

namespace App\Filament\AreaTI\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Request;
use App\Models\Department;

class RequestsByDepartment extends ChartWidget
{
    public function getHeading(): ?string
    {
        return __('Requests by Department');
    }

    protected static ?int $sort = 6;

    protected static ?string $maxWidth = 'lg'; // Ajustar ancho

    public static function getColumns(): int
    {
        return 2; // Cambiar columnas
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'width' => 400, // Ancho personalizado
            'height' => 400, // Alto personalizado
        ];
    }

    protected function getData(): array
    {
        // Consulta para contar las solicitudes por departamento
        $data = Request::query()
            ->selectRaw('department_id, COUNT(*) as total')
            ->join('users', 'requests.user_id', '=', 'users.id') // Unir con la tabla users para acceder al departamento
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        // Obtener los nombres de los departamentos
        $departments = Department::whereIn('id', $data->keys())->pluck('nombre', 'id');

        return [
            'datasets' => [
                [
                    'label' => 'Solicitudes',
                    'data' => $data->values(), // Cantidad de requests por departamento
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9C27B0'], // Colores opcionales
                ],
            ],
            'labels' => $departments->values(), // Nombres de los departamentos
        ];
    }

    protected function getType(): string
    {
        return 'pie'; // Gráfico de pastel
    }
}
