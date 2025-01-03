<?php

namespace App\Filament\Resources\RequestResource\Pages;

use Filament\Actions;
use App\Models\ProductUnit;
use App\Models\RequestProductUnit;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\RequestResource;

class CreateRequest extends CreateRecord
{
    protected static string $resource = RequestResource::class;

    protected function afterCreate(): void
    {
        $cantidadSolicitada = $this->record->cantidad_solicitada;

        $productosDisponibles = ProductUnit::query()
            ->where('product_id', $this->record['product_id'])
            ->where('estado', 'disponible')
            ->take($cantidadSolicitada) // Limitar cierta cantidad de registros
            ->get();

        foreach ($productosDisponibles as $productoItem) {

            $productoItem->update([
                'estado' => 'reservado'
            ]);

            RequestProductUnit::create([
                'request_id' => $this->record->id,
                'product_unit_id' => $productoItem->id,
            ]);
        }
    }
}
