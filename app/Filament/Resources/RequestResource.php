<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Request;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Traits\RequestResourceTrait;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\RequestResource\Pages;

class RequestResource extends Resource
{
    use RequestResourceTrait;

    protected static ?string $model = Request::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

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
                            ->disabled(fn($record) => $record === null) // Desactivado si estamos en creacion
                            ->dehydrated(true) // Esto evita que el campo se intente guardar en la base de datos
                            ->default('pendiente')
                            ->options(function (Get $get, ?Request $record) {
                                $options = [
                                    'pendiente' => __('Pending'),
                                    'aceptado' => __('Accepted'),
                                    'rechazado' => __('Rejected'),
                                ];

                                if ($record !== null && !empty($record->estado)) {
                                    if (in_array($record->estado, ['aceptado', 'completado'])) {
                                        $options['completado'] = __('Completed');
                                    }
                                } else {
                                    $options['completado'] = __('Completed');
                                }

                                return $options;
                            })
                            ->required(),

                        Section::make('Razones de Rechazo')
                            ->label(__('Reasons for Rejection'))
                            ->schema([
                                Forms\Components\Textarea::make('motivo_rechazo')
                                    ->required()
                                    ->label('Motivo Rechazo')
                            ])
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
                                        //withTrashed() para incluir registros eliminados
                                        $productUnit = $record->productUnit()->withTrashed()->first();

                                        //Verificar si $productUnit es nulo antes de acceder a sus propiedades
                                        return $productUnit ? $productUnit->product()->withTrashed()->first()->nombre ?? ' ' : ' ';
                                    }),
                                Forms\Components\TextInput::make('unit_marca')
                                    ->disabled()
                                    ->label(__('Brand'))
                                    ->formatStateUsing(function ($state, $record) {
                                        //withTrashed() para incluir registros eliminados
                                        $productUnit = $record->productUnit()->withTrashed()->first();

                                        //Verificar si $productUnit es nulo antes de acceder a sus propiedades
                                        return $productUnit ? $productUnit->product()->withTrashed()->first()->marca ?? ' ' : ' ';
                                    }),
                                Forms\Components\TextInput::make('unit_modelo')
                                    ->disabled()
                                    ->label(__('Model'))
                                    ->formatStateUsing(function ($state, $record) {

                                        //withTrashed() para incluir registros eliminados
                                        $productUnit = $record->productUnit()->withTrashed()->first();

                                        //Verificar si $productUnit es nulo antes de acceder a sus propiedades
                                        return $productUnit ? $productUnit->product()->withTrashed()->first()->modelo ?? ' ' : ' ';
                                    }),
                                Forms\Components\TextInput::make('unit_codigo_inventario')
                                    ->disabled()
                                    ->label(__('Stock Code'))
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit()->withTrashed()->first()->codigo_inventario ?? ' ';
                                    }),
                                Forms\Components\TextInput::make('unit_serie')
                                    ->disabled()
                                    ->label(__('Series'))
                                    ->formatStateUsing(function ($state, $record) {
                                        return $record->productUnit()->withTrashed()->first()->codigo_inventario ?? ' ';
                                    }),

                                Hidden::make('product_unit_id')
                            ])
                            ->columns(5)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {

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
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return optional($record->user()->withTrashed()->first())->name;
                    }),
                Tables\Columns\TextColumn::make('articulos_prestados')
                    ->label(__('Borrowed Items'))
                    ->getStateUsing(function ($record) {

                        return RequestResourceTrait::formatRequestedArticles($record);
                    })
                    ->badge()
                    ->separator(',')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('cantidad_solicitada')
                    ->label(__('Requested Quantity'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label(__('State'))
                    ->badge()
                    ->color(fn(string $state): string => RequestResourceTrait::getStateColor($state))
                    ->icon(fn(string $state): string => RequestResourceTrait::getStateIcon($state))
                    ->formatStateUsing(fn(string $state): string => ucfirst(__($state))), // Traduce el estado,
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
                Tables\Actions\ViewAction::make(),
                ActionGroup::make([
                    // Array of actions
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->before(fn($record) => RequestResourceTrait::beforeDelete($record))
                        ->after(fn($record) => RequestResourceTrait::afterDelete($record)),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\RequestUnitsRelationManager::class,
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
