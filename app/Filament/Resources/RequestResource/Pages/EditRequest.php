<?php

namespace App\Filament\Resources\RequestResource\Pages;

use Filament\Actions;
use App\Models\Request;
use App\Models\ProductUnit;
use App\Models\RequestProductUnit;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\RequestResource;

class EditRequest extends EditRecord
{
    protected static string $resource = RequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave()
    {
        $cantidadSolicitadaEnFormulario = $this->data['cantidad_solicitada'];

        $cantidadPreviamenteSolicitada = $this->record->cantidad_solicitada;



        if ($cantidadSolicitadaEnFormulario > $cantidadPreviamenteSolicitada) {

            $cantidadProductosAddRequest = $cantidadSolicitadaEnFormulario - $cantidadPreviamenteSolicitada;

            $productosDisponibles = ProductUnit::query()
                ->where('product_id', $this->record->product_id)
                ->where('estado', 'disponible')
                ->take($cantidadProductosAddRequest) // Limitar cierta cantidad de registros
                ->get();

            // dd($productosDisponibles);

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

        if ($cantidadSolicitadaEnFormulario < $cantidadPreviamenteSolicitada) {

            $cantidadProductosDesasociarRequest = $cantidadPreviamenteSolicitada - $cantidadSolicitadaEnFormulario;

            $productosReservadosId = Request::query()
                ->where('requests.id', $this->record->id)
                ->join('request_product_units', 'requests.id', '=', 'request_product_units.request_id')
                ->pluck('product_unit_id');

            $productosReservados = ProductUnit::query()
                ->whereIn('id', $productosReservadosId)
                ->take($cantidadProductosDesasociarRequest)
                ->get();

            foreach ($productosReservados as $productoItem) {

                $productoItem->update([
                    'estado' => 'disponible'
                ]);

                RequestProductUnit::query()
                    ->where('request_id', $this->record->id)
                    ->where('product_unit_id', $productoItem->id)
                    ->delete();
            }
        }
    }

    protected function afterSave()
    {
        // $productosReservadosId = Request::query()
        //     ->where('requests.id', $this->record->id)
        //     ->join('request_product_units', 'requests.id', '=', 'request_product_units.request_id')
        //     ->pluck('product_unit_id');

        // $productosReservados = ProductUnit::query()
        //     ->whereIn('id', $productosReservadosId)
        //     ->get();

        if ($this->record->estado == 'pendiente') {

            $this->record->requestProductUnits->map(function ($requestProductUnit) {
                $requestProductUnit->productUnit->estado = 'reservado';
                $requestProductUnit->productUnit->save();
            });
        }

        if ($this->record->estado == 'aceptado') {

            $this->record->requestProductUnits->map(function ($requestProductUnit) {
                $requestProductUnit->productUnit->estado = 'prestado';
                $requestProductUnit->productUnit->save();
            });
        }

        if ($this->record->estado == 'rechazado' || $this->record->estado == 'completado') {

            $this->record->requestProductUnits->map(function ($requestProductUnit) {
                $requestProductUnit->productUnit->estado = 'disponible';
                $requestProductUnit->productUnit->save();
            });
        }
    }
}
