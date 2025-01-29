<?php

namespace App\Traits;

use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\ProductUnit;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

trait RequestResourceTrait
{
    public static function calculateNewAvailableQuantity(Get $get, Set $set): void
    {
        $selectedProducts = $get('requestProductUnits');
        $selectedProduct = $get('general_product');

        $counter = 0;

        foreach ($selectedProducts as $product) {
            if ($selectedProduct == ProductUnit::find($product['product_unit_id'])->product_id) {
                $counter++;
            }
        }

        $availableQuantity = self::calculateAvailableQuantity($selectedProduct);

        $newAvailableQuantity = $availableQuantity - $counter;

        $set('cantidad_disponible', $newAvailableQuantity);
    }

    public static function calculateAvailableQuantity(string $productId)
    {
        $quantity = ProductUnit::query()
            ->where('product_id', $productId)
            ->where('estado', 'disponible')
            ->count();

        return $quantity;
    }

    public static function getSelectedProductOptions(Get $get)
    {
        $selectedProducts = $get('requestProductUnits');

        $selectedProductsID = [];

        foreach ($selectedProducts as $selectedProduct) {
            array_push($selectedProductsID, $selectedProduct['product_unit_id']);
        }

        return ProductUnit::query()
            ->where('estado', 'disponible')
            ->where('product_id', $get('general_product'))
            ->whereNot(function (Builder $query) use ($selectedProductsID) {
                $query->whereIn('id', $selectedProductsID);
            })
            ->with('product')
            ->get()
            ->mapWithKeys(function ($productUnit) {
                return [
                    $productUnit->id => "ID: {$productUnit->id} - Producto: {$productUnit->product->nombre}"
                ];
            });
    }

    public static function addItemToRepeater($state, Get $get, Set $set)
    {
        if (!$state) return;

        $requestQuatity = $get('cantidad_solicitada');

        $set('cantidad_solicitada', $requestQuatity + 1);

        $productUnit = ProductUnit::find($state);

        if ($productUnit) {

            $addRepeaterData = [
                'unit_nombre' => $productUnit->product->nombre ?? '',
                'unit_marca' => $productUnit->product->marca ?? '',
                'unit_modelo' => $productUnit->product->modelo ?? '',
                'unit_codigo_inventario' => $productUnit->codigo_inventario ?? '',
                'unit_serie' => $productUnit->serie ?? '',
                'product_unit_id' => $productUnit->id,
            ];

            $actualRepeaterData = $get('requestProductUnits') ?? [];

            array_push($actualRepeaterData, $addRepeaterData);

            $set('requestProductUnits', $actualRepeaterData);
        }
    }

    public static function formatRequestedArticles($record)
    {
        // Obtener los productos y la cantidad solicitada
        $articles = $record->requestProductUnits->map(function ($requestProductUnit) {
            $productName = $requestProductUnit->productUnit->product->nombre ?? 'Producto desconocido';
            return $productName;
        });

        $groupedArticles = $articles->countBy();

        // Combina los nombres de los artículos en una cadena y los separa por comas
        return $groupedArticles->map(function ($quantity, $article) {
            return "{$article} x {$quantity}";
        })->join(', ');
    }

    public static function getStateColor(string $state): string
    {
        return match ($state) {
            'pendiente' => 'warning',
            'aceptado' => 'success',
            'rechazado' => 'danger',
            'completado' => 'info',
            default => 'secondary',
        };
    }

    public static function getStateIcon(string $state): string
    {
        return match ($state) {
            'pendiente' => 'heroicon-o-clock',
            'aceptado' => 'heroicon-o-check-circle',
            'rechazado' => 'heroicon-o-x-circle',
            'completado' => 'heroicon-o-check-badge',
            default => 'heroicon-o-question-mark-circle',
        };
    }

    public static function beforeDelete($record)
    {
        // Obtén los product_unit_id antes de que se elimine el registro
        $productUnitIds = $record->requestProductUnits->pluck('product_unit_id');

        // Guarda los IDs en el registro para acceder en el after
        $record->setRelation('product_unit_ids_to_update', $productUnitIds);
    }

    public static function afterDelete($record)
    {
        // Se obtienen los datos guardados en la relacion establecida antes de eliminar el request
        $productUnitIds = $record->getRelation('product_unit_ids_to_update');

        if (in_array($record->estado, ['pendiente', 'aceptado'])) {
            ProductUnit::query()
                ->whereIn('id', $productUnitIds)
                ->update(['estado' => 'disponible']);
        }
    }

    public static function sendNotification($record)
    {
        $recipient = User::where('id', $record->user_id)->get();

        $state = match ($record->estado) {
            'aceptado' => 'Accepted',
            'rechazado' => 'Declined',
            'completado' => 'Completed',
        };

        Notification::make()
            ->title(__("Your Request from " . $record->created_at->format('d/m/Y H:i') . " has been " . ucfirst($state)))
            ->icon('heroicon-o-clipboard-document')
            ->iconColor(match ($record->estado) {
                'aceptado' => 'success',
                'rechazado' => 'danger',
                'completado' => 'info',
                default => 'gray',
            })
            ->actions([
                Action::make('view')
                    ->label('View Request')
                    ->button()
                    ->url(\App\Filament\AreaTI\Resources\RequestResource::getUrl('edit', ['record' => $record->id], panel: 'personal')),
                // ->openUrlInNewTab(),
            ])
            ->sendToDatabase($recipient);
    }
}
