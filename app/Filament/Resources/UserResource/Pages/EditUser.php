<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Actions;
use App\Filament\Resources\UserResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record) {

                    if ($record->requests->isNotEmpty()) {

                        // Unidades reservadas encontradas, impedir la eliminación y mostrar un error.
                        Notification::make()
                            ->title('Error')
                            ->body('No se puede eliminar este elemento porque tiene peticiones asociadas.')
                            ->danger()
                            ->send();


                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'error' => 'No se puede eliminar este elemento porque tiene peticiones asociadas.',
                        ]);
                    }
                }),
        ];
    }
}
