<?php

namespace App\Filament\Personal\Resources\RequestResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\ProductUnit;
use App\Models\RequestProductUnit;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Personal\Resources\RequestResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Facades\Filament;

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

        //Send Notification to IT Users
        $recipients = User::whereHas('roles', function ($query) {
            $query->where('name', 'area_ti');
        })->get();

        Notification::make()
            ->title(__('A Request Have Been Created'))
            ->icon('heroicon-o-clipboard-document')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('View Request')
                    ->button()
                    ->url(RequestResource::getUrl('edit', ['record' => $this->record->id], panel: 'areaTI')),
                // ->openUrlInNewTab(),
            ])
            ->sendToDatabase($recipients);
    }
}
