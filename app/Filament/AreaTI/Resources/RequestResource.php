<?php

namespace App\Filament\AreaTI\Resources;

use Closure;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Request;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductUnit;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Actions\StaticAction;
use App\Traits\RequestResourceTrait;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\AreaTI\Resources\RequestResource\Pages;
use App\Filament\AreaTI\Resources\RequestResource\RelationManagers;

class RequestResource extends Resource
{
    use RequestResourceTrait;

    protected static ?string $model = Request::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    // protected static ?string $navigationLabel = 'Peticiones';

    // Con este metodo se sobreescribe el label que usa Filament para establecer nombres del recurso a traves de toda la UI
    public static function getModelLabel(): string
    {
        return __('Request');
    }

    // Con este metodo se sobreescribe el label que usa Filament para establecer nombres del recurso a traves de toda la UI
    public static function getPluralModelLabel(): string
    {
        return __('Requests');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('created_at', 'desc');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('estado', 'pendiente')->count() > 1 ? 'danger' : 'warning';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('estado', 'pendiente')->count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Gestión de Productos Solicitados')
                    ->schema([

                        Forms\Components\Select::make('user_id')
                            ->label(__('User'))
                            ->relationship('user', 'name')
                            ->disabled(fn($record) => $record !== null) // Desactivado si estamos en edición
                            ->required(),

                        Forms\Components\Select::make('general_product')
                            ->label(__('Product'))
                            ->live()
                            ->relationship('requestProductUnits.productUnit.product', 'nombre')
                            ->afterStateUpdated(function (Set $set, Get $get) {

                                $set('selected_products', null);

                                RequestResourceTrait::calculateNewAvailableQuantity($get, $set);
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn($record) => $record !== null) // Desactivado si estamos en edición
                            ->visible(fn($record) => $record === null) // No visible si estamos en edición
                            ->dehydrated(false), // Esto evita que el campo se intente guardar en la base de datos,

                        Forms\Components\Select::make('selected_products')
                            ->label(__('Select Products'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn($record) => $record !== null) // Desactivado si estamos en edición
                            ->visible(fn($record) => $record === null) // No visible si estamos en edición
                            ->dehydrated(false) // Esto evita que el campo se intente guardar en la base de datos
                            ->options(function (Get $get) {

                                return RequestResourceTrait::getSelectedProductOptions($get);
                            })
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {

                                RequestResourceTrait::addItemToRepeater($state, $get, $set);

                                RequestResourceTrait::calculateNewAvailableQuantity($get, $set);

                                // Se limpia el campo
                                $set('selected_products', null);
                            }),

                        Forms\Components\TextInput::make('cantidad_disponible')
                            ->live()
                            ->label(__('Available Quantity'))
                            ->disabled()
                            ->visible(fn($record) => $record === null) // No visible si estamos en edición
                            ->numeric(),

                        Forms\Components\TextInput::make('cantidad_solicitada')
                            ->label(__('Requested Quantity'))
                            ->default(0)
                            ->disabled()
                            ->dehydrated(true) // Asegura que el valor se envíe a la base de datos
                            ->numeric(),

                        Forms\Components\Select::make('estado')
                            ->label(__('State'))
                            ->live()
                            ->disabled(true) // Desactivado si estamos en creacion
                            ->dehydrated(true) // Esto evita que el campo se intente guardar en la base de datos
                            ->default('pendiente')
                            ->options([
                                'pendiente' => __('Pending'),
                                'aceptado' => __('Accepted'),
                                'rechazado' => __('Rejected'),
                                'completado' => __('Completed'),
                            ])
                            ->required(),

                        Section::make('Razones de Rechazo')
                            ->label(__('Reasons for Rejection'))
                            ->schema([
                                Forms\Components\Textarea::make('motivo_rechazo')
                                    ->required()
                                    ->label('Motivo Rechazo')
                            ])
                            ->disabled(true)
                            ->visible(fn(Get $get) => $get('estado') === 'rechazado')

                    ])->columns(3),

                Section::make(__('Selected Products'))
                    ->schema([
                        Repeater::make('requestProductUnits')
                            ->label('')
                            ->relationship('requestProductUnits')
                            ->schema([
                                Forms\Components\TextInput::make('unit_nombre')
                                    ->disabled()
                                    ->label(__('Name'))
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->product->nombre ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_marca')
                                    ->disabled()
                                    ->label(__('Brand'))
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->product->marca ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_modelo')
                                    ->disabled()
                                    ->label(__('Model'))
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->product->modelo ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_codigo_inventario')
                                    ->disabled()
                                    ->label(__('Stock Code'))
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->codigo_inventario ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_serie')
                                    ->disabled()
                                    ->label(__('Series'))
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->serie ?? 0;
                                    }),

                                Hidden::make('product_unit_id')
                            ])
                            ->columns(5)
                            ->afterStateUpdated(function (Set $set, Get $get) {

                                $set('cantidad_solicitada', $get('cantidad_solicitada') - 1);

                                RequestResourceTrait::calculateNewAvailableQuantity($get, $set);
                            })
                            ->afterStateHydrated(function (Set $set, Get $get) {})
                            // ->collapsible()
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(fn($record) => $record === null) // Eliminacion desactivada en edicion
                            ->columnSpan(2)
                            ->addActionLabel('Añadir Producto')
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, Closure $fail) {
                                        if (count($value) === 0) {
                                            // El $fail es lo que evita que la peticion se cree.
                                            $fail('');
                                            Notification::make()
                                                ->title(__('You must add at least one product'))
                                                ->danger()
                                                ->send();
                                        }
                                    };
                                },
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('User'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.position.nombre')
                    ->label(__('Position'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.department.nombre')
                    ->label(__('Department'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('articulos_prestados')
                    ->label('Artículos Prestados')
                    ->getStateUsing(function ($record) {

                        return RequestResourceTrait::formatRequestedArticles($record);
                    })
                    ->badge()
                    ->separator(',')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('cantidad_solicitada')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => RequestResourceTrait::getStateColor($state))
                    ->icon(fn(string $state): string => RequestResourceTrait::getStateIcon($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->recordUrl(fn($record) => null) // Se desactiva que las filas sean clickeables
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'aceptado' => __('Accepted'),
                        'rechazado' => __('Rejected'),
                        'completado' => __('Completed'),
                        'pendiente' => __('Pending'),
                    ])
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
                    ->label(__('Accept'))
                    ->action(function ($record) {

                        // Se cambia el estado de los productos asociados al request a prestado
                        $record->requestProductUnits->map(function ($requestProductUnit) {
                            $requestProductUnit->productUnit->estado = 'prestado';
                            $requestProductUnit->productUnit->save();
                        });

                        // Se cambia el estado del request a aceptado
                        $record->estado = 'aceptado';
                        $record->save();

                        //Send Notification
                        RequestResourceTrait::sendNotification($record);

                        // Send Email
                        RequestResourceTrait::sendNotificationEmailRequestApproved($record);
                    })
                    ->button()
                    ->requiresConfirmation() // Opcional: Confirmación antes de ejecutar
                    ->modalHeading('Aceptar Solicitud') // Título del modal
                    ->modalDescription('Está seguro que desea aceptar esta solicitud? Esta acción no puede ser desecha. Antes de aceptar la solicitud, recuerde tener a la mano el Carnet del prestatario.')
                    ->modalSubmitActionLabel('Si, Aceptar')
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar'))
                    ->slideOver()
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
                    ->label(__('Decline'))
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

                        //Send Notification
                        RequestResourceTrait::sendNotification($record);

                        // Send Email
                        RequestResourceTrait::sendNotificationEmailRequestRejected($record, $data['motivo_rechazo']);
                    })
                    ->form([ // Campos del formulario en el modal
                        Forms\Components\Textarea::make('motivo_rechazo')
                            ->label('Motivo del rechazo')
                            ->required(), // Campo obligatorio
                    ])
                    ->requiresConfirmation() // Opcional: Confirmación antes de ejecutar
                    ->modalHeading('Rechazar Solicitud') // Título del modal
                    ->modalDescription('Está seguro que desea rechazar esta solicitud? Esta acción no puede ser desecha.')
                    ->modalSubmitActionLabel('Si, Rechazar')
                    ->modalIcon('heroicon-o-x-circle')
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar'))
                    ->slideOver()
                    ->button()
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

                        //Send Notification
                        RequestResourceTrait::sendNotification($record);

                        // Send Email
                        RequestResourceTrait::sendNotificationEmailRequestCompleted($record);
                    })
                    ->button()
                    ->requiresConfirmation() // Opcional: Confirmación antes de ejecutar
                    ->modalHeading('Completar Solicitud') // Título del modal
                    ->modalDescription('Está seguro que desea completar esta solicitud? Esta acción no puede ser desecha. Completar la solicitud, significa que el prestatario ya ha devuelto los productos prestados y que se le ha devuelto su Carnet.')
                    ->modalSubmitActionLabel('Si, Completar')
                    ->modalIcon('heroicon-o-check-badge')
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar'))
                    ->slideOver()
                    ->color('info'),

                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            // ->recordUrl(fn($record) => null) // Desactiva el clic en la fila

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
            'view' => Pages\ViewRequest::route('/{record}'),
        ];
    }
}
