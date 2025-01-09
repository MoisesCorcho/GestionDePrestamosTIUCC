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

    protected function beforeCreate(): void
    {
        // dd($this->data);
    }

    protected function afterCreate(): void
    {
        $requestedProductsId = $this->data['selected_products'];

        ProductUnit::query()
            ->whereIn('id', $requestedProductsId)
            ->update([
                'estado' => 'reservado'
            ]);
    }
}
