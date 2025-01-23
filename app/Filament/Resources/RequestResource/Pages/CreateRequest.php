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
        $requestedProducts = $this->data['requestProductUnits'];

        $requestedProductsIDs = [];

        foreach ($requestedProducts as $requstProduct) {
            array_push($requestedProductsIDs, $requstProduct['product_unit_id']);
        }

        ProductUnit::query()
            ->whereIn('id', $requestedProductsIDs)
            ->update([
                'estado' => 'reservado'
            ]);
    }
}
