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

    protected function beforeSave() {}

    protected function afterSave()
    {
        if ($this->record->estado !== 'rechazado') {
            $this->record->motivo_rechazo = null;
            $this->record->save();
        }

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
