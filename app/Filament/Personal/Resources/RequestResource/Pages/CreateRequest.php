<?php

namespace App\Filament\Personal\Resources\RequestResource\Pages;

use App\Models\User;
use Filament\Actions;
use App\Models\ProductUnit;
use Filament\Facades\Filament;
use App\Models\RequestProductUnit;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Personal\Resources\RequestResource;
// use App\Mail\NewRequest;
use Illuminate\Support\Facades\Notification as EmailNotification;
use App\Notifications\NewRequest;

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

        $requestPath = RequestResource::getUrl('view', ['record' => $this->record->id], panel: 'areaTI');

        Notification::make()
            ->title(__('Una nueva petición ha sido creada'))
            ->icon('heroicon-o-clipboard-document')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('Ver Petición')
                    ->button()
                    ->url($requestPath),
                // ->openUrlInNewTab(),
            ])
            ->sendToDatabase($recipients);

        $products = [];

        foreach ($this->data['requestProductUnits'] as $unitProduct) {
            array_push($products, $unitProduct);
        }

        $data = array(
            'products' => $products,
            'user' => auth()->user(),
            'request_path' => $requestPath,
        );

        EmailNotification::send($recipients, new NewRequest($data));
        // Mail::to($recipients)->send(new NewRequest($data));
    }
}
