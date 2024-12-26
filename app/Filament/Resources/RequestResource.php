<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use App\Models\Request;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ProductUnit;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\RequestResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\RequestResource\RelationManagers;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function afterSave(): void
    {
        // Lógica personalizada después de guardar el registro
        // Por ejemplo, enviar una notificación o actualizar otros modelos
        dd('asdasd');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),

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
                    ->label('Cantidad Disponible')
                    ->disabled()
                    ->numeric(),

                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'aceptado' => 'Aceptado',
                        'rechazado' => 'Rechazado',
                        'completado' => 'Completado',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('motivo_rechazo')
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
                        'completado' => 'danger',
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
                Tables\Actions\DeleteAction::make(),
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
            RelationManagers\RequestUnitsRelationManager::class,
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
