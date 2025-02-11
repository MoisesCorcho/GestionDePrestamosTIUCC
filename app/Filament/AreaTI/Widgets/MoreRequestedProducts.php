<?php

namespace App\Filament\AreaTI\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MoreRequestedProducts extends ChartWidget
{
    protected static ?string $heading = 'Productos mas Solicitados';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $topProducts = Product::select('products.nombre', DB::raw('COUNT(request_product_units.id) as total_requests'))
            ->join('product_units', 'products.id', '=', 'product_units.product_id')
            ->join('request_product_units', 'product_units.id', '=', 'request_product_units.product_unit_id')
            ->join('requests', 'request_product_units.request_id', '=', 'requests.id')
            ->where('requests.estado', '!=', 'rechazado') // Opcional: Excluir los rechazados
            ->groupBy('products.id', 'products.nombre')
            ->orderByDesc('total_requests')
            ->limit(5) // Obtener los 10 productos más pedidos
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Requests',
                    'data' => $topProducts->pluck('total_requests')->toArray(),
                ],
            ],
            'labels' => $topProducts->pluck('nombre')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
