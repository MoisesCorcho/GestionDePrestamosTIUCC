<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\ProductResource;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {

                    $requestedUnits = $record->units->filter(function ($unit) {
                        return $unit->estado === 'reservado';
                    });

                    if ($requestedUnits->isNotEmpty()) {

                        // Unidades reservadas encontradas, impedir la eliminación y mostrar un error.
                        Notification::make()
                            ->title('Error')
                            ->body('No se puede eliminar este elemento porque tiene unidades reservadas.')
                            ->danger()
                            ->send();


                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'error' => 'No se puede eliminar este elemento porque tiene unidades reservadas.',
                        ]);
                    } else {
                        // Si no hay unidades reservadas, puedes enviar una notificación de éxito.

                        Notification::make()
                            ->title('Éxito')
                            ->body('Elemento eliminado correctamente.')
                            ->success()
                            ->send();
                    }
                }),
            Actions\RestoreAction::make(),
            // ...
        ];
    }
}
