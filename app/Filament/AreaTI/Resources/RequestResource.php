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
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\AreaTI\Resources\RequestResource\Pages;
use App\Filament\AreaTI\Resources\RequestResource\RelationManagers;

class RequestResource extends Resource
{
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Gestión de Productos Solicitados')
                    ->schema([

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(fn($record) => $record !== null) // Desactivado si estamos en edición
                            ->required(),

                        Forms\Components\Select::make('general_product')
                            ->live()
                            ->relationship('requestProductUnits.productUnit.product', 'nombre')
                            ->afterStateUpdated(function (Set $set, Get $get) {

                                $cantidadDisponible = ProductUnit::query()
                                    ->where('product_id', $get('general_product'))
                                    ->where('estado', 'disponible')
                                    ->count();

                                $set('cantidad_disponible', $cantidadDisponible);
                            })
                            ->searchable()
                            ->preload()
                            ->disabled(fn($record) => $record !== null) // Desactivado si estamos en edición
                            ->visible(fn($record) => $record === null) // No visible si estamos en edición
                            ->dehydrated(false), // Esto evita que el campo se intente guardar en la base de datos,

                        Forms\Components\Select::make('selected_products')
                            ->label('Seleccionar Productos')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn($record) => $record !== null) // Desactivado si estamos en edición
                            ->visible(fn($record) => $record === null) // No visible si estamos en edición
                            ->dehydrated(false) // Esto evita que el campo se intente guardar en la base de datos
                            ->options(function (Get $get) {
                                return ProductUnit::query()
                                    ->where('estado', 'disponible')
                                    ->where('product_id', $get('general_product'))
                                    ->with('product')
                                    ->get()
                                    ->mapWithKeys(function ($productUnit) {
                                        return [
                                            $productUnit->id => "ID: {$productUnit->id} - Producto: {$productUnit->product->nombre}"
                                        ];
                                    });
                            })
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {

                                if (!$state) return;

                                $producto = ProductUnit::find($state)[0];

                                if ($producto) {

                                    // se establece en la cantidad solicitada la cantidad de objetos elegido en este campo 'selected_products'
                                    $set('cantidad_solicitada', count($state));

                                    // Se cuentan la cantidad de productos seleccionados que concuerden con el producto general seleccionado
                                    $contarProductoActual = ProductUnit::query()
                                        ->where('product_id', $get('general_product'))
                                        ->whereIn('id', $state)
                                        ->count();

                                    // Se obtiene la cantidad de productos disponibles
                                    $cantidadDisponible = ProductUnit::query()
                                        ->where('product_id', $get('general_product'))
                                        ->where('estado', 'disponible')
                                        ->count();

                                    // A la cantidad disponible del producto general se le restan los que actualmente se han escogido
                                    $cantidadDisponible -= $contarProductoActual;

                                    // Se establece la cantidad disponible en el campo llamado 'cantidad_disponible'
                                    $set('cantidad_disponible', $cantidadDisponible);

                                    // Se obtienen los productos que han sido seleccionados en el campo 'selected_products'
                                    $selectedProducts = ProductUnit::whereIn('id', $state)->get();

                                    // Se mapea/recorre el array de $selectedProducts y por cada cual se establece un nuevo Repeater
                                    if ($selectedProducts->isNotEmpty()) {
                                        $repeaterData = $selectedProducts->map(function ($productUnit) {
                                            return [
                                                'unit_nombre' => $productUnit->product->nombre ?? '',
                                                'unit_marca' => $productUnit->product->marca ?? '',
                                                'unit_modelo' => $productUnit->product->modelo ?? '',
                                                'unit_codigo_inventario' => $productUnit->codigo_inventario ?? '',
                                                'unit_serie' => $productUnit->serie ?? '',
                                                'product_unit_id' => $productUnit->id,
                                            ];
                                        })->toArray();

                                        $set('requestProductUnits', $repeaterData);
                                    }
                                }
                            }),

                        Forms\Components\TextInput::make('cantidad_disponible')
                            ->live()
                            ->label('Cantidad Disponible')
                            ->visible(fn($record) => $record === null) // No visible si estamos en edición
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('cantidad_solicitada')
                            ->disabled()
                            ->dehydrated(true) // Asegura que el valor se envíe a la base de datos
                            ->numeric(),

                        Forms\Components\Select::make('estado')
                            ->live()
                            ->disabled(true) // Desactivado si estamos en creacion
                            ->dehydrated(true) // Esto evita que el campo se intente guardar en la base de datos
                            ->default('pendiente')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'aceptado' => 'Aceptado',
                                'rechazado' => 'Rechazado',
                                'completado' => 'Completado',
                            ])
                            ->required(),

                        Section::make('Razones de Rechazo')
                            ->schema([
                                Forms\Components\TextArea::make('motivo_rechazo')
                                    ->label('')
                            ])
                            ->visible(fn(Get $get) => $get('estado') === 'rechazado')
                            ->disabled(fn($record) => $record !== null) // Desactivado si estamos en edicion

                    ])->columns(3),

                Section::make('Productos Seleccionados')
                    ->schema([
                        Repeater::make('requestProductUnits')
                            ->label('')
                            ->relationship('requestProductUnits')
                            ->schema([
                                Forms\Components\TextInput::make('unit_nombre')
                                    ->disabled()
                                    ->label('Nombre')
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->product->nombre ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_marca')
                                    ->disabled()
                                    ->label('Marca')
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->product->marca ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_modelo')
                                    ->disabled()
                                    ->label('Modelo')
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->product->modelo ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_codigo_inventario')
                                    ->disabled()
                                    ->label('Codigo Inventario')
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->codigo_inventario ?? 0;
                                    }),
                                Forms\Components\TextInput::make('unit_serie')
                                    ->disabled()
                                    ->label('Serie')
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit->serie ?? 0;
                                    }),

                                Hidden::make('product_unit_id')
                            ])
                            ->columns(5)
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
                            // ->collapsible()
                            ->defaultItems(0)
                            ->addable(false)
                            ->deletable(fn($record) => $record === null) // Eliminacion desactivada en edicion
                            ->columnSpan(2)
                            ->addActionLabel('Añadir Producto'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('articulos_prestados')
                    ->label('Artículos Prestados')
                    ->getStateUsing(function ($record) {

                        // Obtener los productos y la cantidad solicitada
                        $articles = $record->requestProductUnits->map(function ($requestProductUnit) {
                            $productName = $requestProductUnit->productUnit->product->nombre ?? 'Producto desconocido';

                            return "{$productName}";
                        });

                        $groupedArticles = $articles->countBy();

                        $articles = $groupedArticles->map(function ($quantity, $article) {
                            return "{$article} x {$quantity}";
                        });

                        // Combina los nombres de los artículos en una cadena
                        return $articles->join(', ');
                    })
                    ->badge()
                    ->separator(',')
                    ->color('primary'),

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
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'pendiente' => 'heroicon-o-clock', // Reloj para indicar espera
                        'aceptado' => 'heroicon-o-check-circle', // Círculo con check para indicar aceptación
                        'rechazado' => 'heroicon-o-x-circle', // Círculo con una X para indicar rechazo
                        'completado' => 'heroicon-o-check-badge', // Insignia con check para indicar finalización
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
            // ->recordUrl(fn($record) => null) // Se desactiva que las filas sean clickeables
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

                        // Opcional: Notificación
                        Notification::make()
                            ->title('Solicitud Completada')
                            ->success()
                            ->send();
                    })
                    ->button()
                    ->requiresConfirmation() // Opcional: Confirmación antes de ejecutar
                    ->modalHeading('Completar Solicitud') // Título del modal
                    ->modalDescription('Está seguro que desea completar esta solicitud? Esta acción no puede ser desecha. Completar la solicitud, significa que el prestatario ya ha devuelto los productos prestados y que se le ha devuelto su carnet.')
                    ->modalSubmitActionLabel('Si, Completar')
                    ->modalIcon('heroicon-o-check-badge')
                    ->modalCancelAction(fn(StaticAction $action) => $action->label('Cerrar'))
                    ->slideOver()
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
