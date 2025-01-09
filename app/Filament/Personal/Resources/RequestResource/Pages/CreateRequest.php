<?php

namespace App\Filament\Personal\Resources\RequestResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\ProductUnit;
use App\Models\RequestProductUnit;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Personal\Resources\RequestResource;

class CreateRequest extends CreateRecord
{
    protected static string $resource = RequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['estado'] = 'pendiente';

        return $data;
    }

    protected function beforeCreate()
    {
        // return dd($this->data);
    }

    protected function afterCreate(): void
    {
        $cantidadSolicitada = $this->record->cantidad_solicitada;

        $productosDisponibles = ProductUnit::query()
            ->where('product_id', $this->record['product_id'])
            ->where('estado', 'disponible')
            ->take($cantidadSolicitada) // Limitar cierta cantidad de registros
            ->get();

        foreach ($productosDisponibles as $productoItem) {

            // Se actualiza el estado de los productos reservados del stock disponible
            $productoItem->update([
                'estado' => 'reservado'
            ]);

            // Se nutre la tabla pivote entre requests y product_units
            RequestProductUnit::create([
                'request_id' => $this->record->id,
                'product_unit_id' => $productoItem->id,
            ]);
        }
    }
}
