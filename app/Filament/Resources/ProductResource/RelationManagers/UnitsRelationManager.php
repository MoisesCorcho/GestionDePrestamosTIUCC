<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_inventario')
                    ->label('Código de Inventario')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('serie')
                    ->label('Número de Serie')
                    ->nullable()
                    ->maxLength(255),

                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->required()
                    ->options([
                        'disponible' => 'Disponible',
                        'prestado' => 'Prestado',
                        'dañado' => 'Dañado',
                        'reservado' => 'Reservado'
                    ])
                    ->default('disponible'),

                Forms\Components\TextInput::make('descripcion_lugar')
                    ->label('Descripción del Lugar')
                    ->nullable()
                    ->maxLength(255),

                Forms\Components\TextInput::make('funcionario_responsable')
                    ->label('Funcionario Responsable')
                    ->nullable()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->label('Fecha de Asignación')
                    ->nullable()
                    ->displayFormat('Y-m-d')
                    ->weekStartsOnMonday(),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('codigo_inventario')
            ->columns([
                Tables\Columns\TextColumn::make('codigo_inventario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('serie')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'prestado' => 'warning',
                        'dañado' => 'danger',
                        'disponible' => 'success',
                        'reservado' => 'info',
                    }),
                Tables\Columns\TextColumn::make('descripcion_lugar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('funcionario_responsable')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fecha_asignacion')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'prestado' => 'Prestado',
                        'dañado' => 'Dañado',
                        'disponible' => 'Disponible',
                        'reservado' => 'Reservado',
                    ])
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function () {

                        $parentProduct = $this->getOwnerRecord(); // Obtiene el modelo padre

                        $parentProduct->cantidad += 1;
                        $parentProduct->save();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {

                        $parentProduct = $this->getOwnerRecord(); // Obtiene el modelo padre

                        $parentProduct->cantidad -= 1;
                        $parentProduct->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {

                            $numberOfProductsToDelete = $records->count();

                            $parentProduct = $this->getOwnerRecord(); // Obtiene el modelo padre

                            $parentProduct->cantidad -= $numberOfProductsToDelete;
                            $parentProduct->save();
                        }),
                ]),
            ]);
    }
}
