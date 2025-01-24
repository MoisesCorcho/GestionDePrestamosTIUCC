<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Request;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductUnit;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\RequestResourceTrait;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\RequestResource\Pages;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Resources\RequestResource\RelationManagers;


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
                            ->label('Producto')
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
                            ->label('Seleccionar Productos')
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
                            ->label('Cantidad Disponible')
                            ->disabled()
                            ->visible(fn($record) => $record === null) // No visible si estamos en edición
                            ->numeric(),

                        Forms\Components\TextInput::make('cantidad_solicitada')
                            ->default(0)
                            ->disabled()
                            ->dehydrated(true) // Asegura que el valor se envíe a la base de datos
                            ->numeric(),

                        Forms\Components\Select::make('estado')
                            ->live()
                            ->disabled(fn($record) => $record === null) // Desactivado si estamos en creacion
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
                                    ->required()
                                    ->label('Motivo Rechazo')
                            ])
                            ->visible(fn(Get $get) => $get('estado') === 'rechazado')

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
                            ->afterStateUpdated(function (Set $set, Get $get) {

                                RequestResourceTrait::calculateNewAvailableQuantity($get, $set);
                            })
                            ->afterStateHydrated(function (Set $set, Get $get) {})
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
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn($record) => RequestResourceTrait::beforeDelete($record))
                    ->after(fn($record) => RequestResourceTrait::afterDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
        ];
    }
}
