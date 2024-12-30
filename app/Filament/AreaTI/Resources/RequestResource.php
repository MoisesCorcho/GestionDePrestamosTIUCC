<?php

namespace App\Filament\AreaTI\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Request;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductUnit;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\AreaTI\Resources\RequestResource\Pages;
use App\Filament\AreaTI\Resources\RequestResource\RelationManagers;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->disabled()
                    ->required(),

                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'nombre')
                    ->disabled()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {

                        $cantidadDisponible = ProductUnit::query()
                            ->where('product_id', $get('product_id'))
                            ->where('estado', 'disponible')
                            ->count();

                        $set('cantidad_disponible', $cantidadDisponible);
                    })
                    ->required(),

                Forms\Components\TextInput::make('cantidad_solicitada')
                    ->required() // Valida que el campo sea obligatorio
                    ->minValue(1) // No puede bajar de 0
                    ->maxValue(fn(Get $get) => $get('cantidad_disponible') ?? 0) // Máximo igual a la cantidad disponible
                    ->numeric()
                    ->disabled()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        $cantidadDisponible = $get('cantidad_disponible') ?? 0;

                        if ($state > $cantidadDisponible) {

                            Notification::make()
                                ->title('No hay suficiente stock disponible')
                                ->warning()
                                ->send();
                        }
                    }),

                Forms\Components\TextInput::make('cantidad_disponible')
                    ->afterStateHydrated(function (Set $set, Get $get) {
                        $productId = $get('product_id'); // Obtén el producto seleccionado
                        if ($productId) {
                            // Lógica para obtener la cantidad disponible del producto
                            $cantidadDisponible = \App\Models\ProductUnit::query()
                                ->where('product_id', $productId)
                                ->where('estado', 'disponible')
                                ->count();

                            // Establece el estado del campo
                            $set('cantidad_disponible', $cantidadDisponible);
                        }
                    })
                    ->label('Cantidad Disponible')
                    ->disabled()
                    ->numeric(),

                Forms\Components\Select::make('estado')
                    ->disabled()
                    ->live()
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aceptado' => 'Aceptado',
                        'rechazado' => 'Rechazado',
                        'completado' => 'Completado',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('motivo_rechazo')
                    ->visible(function (Get $get) {
                        return $get('estado') == 'rechazado';
                    })
                    ->requiredIf('estado', 'rechazado')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.nombre')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_solicitada')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'aceptado' => 'success',
                        'rechazado' => 'danger',
                        'completado' => 'info',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('aceptar')
                    ->disabled(function ($record) {
                        // El boton de aceptar solo estará activo para request en estado pendiente
                        return !($record->estado == 'pendiente');
                    })
                    ->visible(function ($record) {
                        // El boton de aceptar solo estará visible para request en estado pendiente
                        return $record->estado == 'pendiente';
                    })
                    ->label('Aceptar')
                    ->action(function ($record) {

                        // Se cambia el estado de los productos asociados al request a prestado
                        $record->requestProductUnits->map(function ($requestProductUnit) {
                            $requestProductUnit->productUnit->estado = 'prestado';
                            $requestProductUnit->productUnit->save();
                        });

                        // Se cambia el estado del request a aceptado
                        $record->estado = 'aceptado';
                        $record->save();

                        // Opcional: Notificación
                        Notification::make()
                            ->title('Solicitud Aceptada')
                            ->success()
                            ->send();
                    })
                    ->button()
                    ->requiresConfirmation() // Opcional: Confirmación antes de ejecutar
                    ->color('success'), // Color del botón

                Tables\Actions\Action::make('rechazar')
                    ->disabled(function ($record) {
                        // El boton de rechazar solo estará activo para request en estado pendiente
                        return !($record->estado == 'pendiente');
                    })
                    ->visible(function ($record) {
                        // El boton de rechazar solo estará visible para request en estado pendiente
                        return $record->estado == 'pendiente';
                    })
                    ->label('Rechazar')
                    ->action(function ($record, array $data) {

                        // Se cambia el estado de los productos asociados al request a prestado
                        $record->requestProductUnits->map(function ($requestProductUnit) {
                            $requestProductUnit->productUnit->estado = 'disponible';
                            $requestProductUnit->productUnit->save();
                        });

                        // Se cambia el estado del request a aceptado
                        $record->estado = 'rechazado';
                        $record->motivo_rechazo = $data['motivo_rechazo'];
                        $record->save();

                        // Opcional: Notificación
                        Notification::make()
                            ->title('Solicitud Rechazada')
                            ->success()
                            ->send();
                    })
                    ->form([ // Campos del formulario en el modal
                        Forms\Components\Textarea::make('motivo_rechazo')
                            ->label('Motivo del rechazo')
                            ->required(), // Campo obligatorio
                    ])
                    ->modalHeading('Rechazar solicitud') // Título del modal
                    ->button()
                    ->requiresConfirmation() // Opcional: Confirmación antes de ejecutar
                    ->color('danger'), // Color del botón

                Tables\Actions\Action::make('completar')
                    ->disabled(function ($record) {
                        // El boton de rechazar solo estará activo para request en estado aceptado
                        return !($record->estado == 'aceptado');
                    })
                    ->visible(function ($record) {
                        // El boton de rechazar solo estará activo para request en estado aceptado
                        return $record->estado == 'aceptado';
                    })
                    ->label('Completar')
                    ->action(function ($record) {

                        // Se cambia el estado de los productos asociados al request a prestado
                        $record->requestProductUnits->map(function ($requestProductUnit) {
                            $requestProductUnit->productUnit->estado = 'disponible';
                            $requestProductUnit->productUnit->save();
                        });

                        // Se cambia el estado del request a aceptado
                        $record->estado = 'completado';
                        $record->save();

                        // Opcional: Notificación
                        Notification::make()
                            ->title('Solicitud Completada')
                            ->success()
                            ->send();
                    })
                    ->button()
                    ->requiresConfirmation() // Opcional: Confirmación antes de ejecutar
                    ->color('info'),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
            'create' => Pages\CreateRequest::route('/create'),
            'edit' => Pages\EditRequest::route('/{record}/edit'),
        ];
    }
}
