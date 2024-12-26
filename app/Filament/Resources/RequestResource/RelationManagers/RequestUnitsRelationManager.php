<?php

namespace App\Filament\Resources\RequestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequestUnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'requestUnits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('unit_id')
                    ->relationship('unit', 'codigo_inventario')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_id')
            ->columns([
                Tables\Columns\TextColumn::make('unit.product.nombre')
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('unit.product.marca')
                    ->label('Marca'),
                Tables\Columns\TextColumn::make('unit.product.modelo')
                    ->label('Modelo'),
                Tables\Columns\TextColumn::make('unit.codigo_inventario')
                    ->label('Codigo de Inventario'),
                Tables\Columns\TextColumn::make('unit.serie')
                    ->label('Serie'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
