<?php

namespace App\Filament\Personal\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Request;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductUnit;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Personal\Resources\RequestResource\Pages;

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


                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'nombre')
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

                Forms\Components\Textarea::make('motivo_rechazo')
                    ->visible(function (Get $get) {
                        return $get('estado') == 'rechazado';
                    })
                    ->disabled()
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(function ($record) {
                        return !($record->estado == 'pendiente');
                    })
                    ->before(function ($record) {

                        // Obtén los product_unit_id antes de que se elimine el registro
                        $productUnitIds = $record->requestProductUnits->pluck('product_unit_id');

                        // Guarda los IDs en el registro para acceder en el after
                        $record->setRelation('product_unit_ids_to_update', $productUnitIds);
                    })
                    ->after(function ($record) {

                        // Se obtienen los datos guardados en la relacion establecida antes de eliminar el request
                        $productUnitIds = $record->getRelation('product_unit_ids_to_update');

                        if ($record->estado == 'pendiente' || $record->estado == 'aceptado') {

                            ProductUnit::query()
                                ->whereIn('id', $productUnitIds)
                                ->update(['estado' => 'disponible']);
                        }
                    }),
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
