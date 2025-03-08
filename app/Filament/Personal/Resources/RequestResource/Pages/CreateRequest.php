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
use App\Models\Setting;
use Illuminate\Support\Carbon;
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
        $now = Carbon::now();
        $dayOfWeek = strtolower($now->englishDayOfWeek);

        $setting = Setting::where('dia', $dayOfWeek)->first();

        if (!$setting) {
            Notification::make()
                ->title(__('Schedule not configured for today'))
                ->danger()
                ->send();
            $this->halt();
            return;
        }

        $requestOpeningTime = Carbon::parse($setting->hora_solicitudes_apertura);
        $requestClosingTime = Carbon::parse($setting->hora_solicitudes_cierre);
        $openingTime = Carbon::parse($setting->hora_apertura);
        $breakStartTime = $setting->descanso_inicio ? Carbon::parse($setting->descanso_inicio) : null;
        $breakEndTime = $setting->descanso_fin ? Carbon::parse($setting->descanso_fin) : null;

        // Calcular la diferencia de tiempo en minutos
        $timeDifference = $openingTime->diffInMinutes($requestOpeningTime);

        if ($now->lessThan($requestOpeningTime) || $now->greaterThan($requestClosingTime)) {
            Notification::make()
                ->title(__('Outside request submission hours'))
                ->body(__('Request submission hours are from :opening to :closing', [
                    'opening' => $requestOpeningTime->format('H:i'),
                    'closing' => $requestClosingTime->format('H:i'),
                ]))
                ->danger()
                ->send();
            $this->halt();
            return;
        }

        if ($breakStartTime && $breakEndTime) {
            // Ajustar el horario de descanso
            $adjustedBreakStartTime = $breakStartTime->clone()->subMinutes($timeDifference);

            if ($now->greaterThanOrEqualTo($adjustedBreakStartTime) && $now->lessThan($breakEndTime)) {
                Notification::make()
                    ->title(__('Break time'))
                    ->body(__('Break time is from :start to :end', [
                        'start' => $adjustedBreakStartTime->format('H:i'),
                        'end' => $breakEndTime->format('H:i'),
                    ]))
                    ->danger()
                    ->send();
                $this->halt();
                return;
            }
        }
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
