<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Product;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class ProductAvailability extends BaseWidget
{
    protected static ?string $heading = null;

    public function __construct()
    {
        static::$heading = __('Product Availability');
    }

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.id', 'products.nombre')
                    ->selectSub(
                        DB::table('product_units')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('product_units.product_id', 'products.id')
                            ->where('estado', 'prestado'),
                        'prestado'
                    )
                    ->selectSub(
                        DB::table('product_units')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('product_units.product_id', 'products.id')
                            ->where('estado', 'dañado'),
                        'dañado'
                    )
                    ->selectSub(
                        DB::table('product_units')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('product_units.product_id', 'products.id')
                            ->where('estado', 'disponible'),
                        'disponible'
                    )
                    ->selectSub(
                        DB::table('product_units')
                            ->selectRaw('COUNT(*)')
                            ->whereColumn('product_units.product_id', 'products.id')
                            ->where('estado', 'reservado'),
                        'reservado'
                    )
            )
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label(__('Product'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('prestado')
                    ->label(__('borrowed'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('dañado')
                    ->label(__('damaged'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('disponible')
                    ->label(__('available'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('reservado')
                    ->label(__('reserved'))
                    ->sortable(),
            ])
            ->paginated(5) // Limita los elementos por página
            ->paginationPageOptions([5]) // Solo permite ver 5 registros por página, sin opción de cambiarlo
            ->defaultSort('nombre') // Ordena por nombre
            ->striped(); // Estiliza con filas alternas

    }
}
