<?php

namespace App\Filament\AreaTI\Resources\RequestResource\Pages;

use Filament\Actions;
use App\Models\ProductUnit;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\AreaTI\Resources\RequestResource;
use App\Models\User;

class CreateRequest extends CreateRecord
{
    protected static string $resource = RequestResource::class;

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
