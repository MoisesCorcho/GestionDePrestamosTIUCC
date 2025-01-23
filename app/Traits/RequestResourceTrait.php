<?php

namespace App\Traits;

use Filament\Forms\Get;
use Filament\Forms\Set;

trait RequestResourceTrait
{
    public static function calculateNewAvailableQuantity(Get $get, Set $set): void
    {
        $selectedProducts = $get('requestProductUnits');
        $selectedProduct = $get('general_product');

        $counter = 0;

        foreach ($selectedProducts as $product) {
            if ($selectedProduct == \App\Models\ProductUnit::find($product['product_unit_id'])->product_id) {
                $counter++;
            }
        }

        $availableQuantity = self::calculateAvailableQuantity($selectedProduct);

        $newAvailableQuantity = $availableQuantity - $counter;

        $set('cantidad_disponible', $newAvailableQuantity);
    }

    public static function calculateAvailableQuantity(string $productId)
    {
        $quantity = \App\Models\ProductUnit::query()
            ->where('product_id', $productId)
            ->where('estado', 'disponible')
            ->count();

        return $quantity;
    }
}
