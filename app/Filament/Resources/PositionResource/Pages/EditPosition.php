<?php

namespace App\Filament\Resources\PositionResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\PositionResource;

class EditPosition extends EditRecord
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {

                    if ($record->users->isNotEmpty()) {

                        // Unidades reservadas encontradas, impedir la eliminación y mostrar un error.
                        Notification::make()
                            ->title('Error')
                            ->body('No se puede eliminar este elemento porque tiene usuarios asociados.')
                            ->danger()
                            ->send();


                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'error' => 'No se puede eliminar este elemento porque tiene usuarios asociados.',
                        ]);
                    }
                }),
            Actions\RestoreAction::make(),
        ];
    }
}
