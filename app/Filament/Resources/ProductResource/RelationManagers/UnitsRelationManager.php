<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Doctrine\Inflector\Rules\English\Rules;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('codigo_inventario')
                    ->label('Código de Inventario')
                    ->required()
                    ->maxLength(255)
                    ->rules(function ($record) {
                        return [
                            'required',
                            'max:255',
                            Rule::unique('product_units', 'codigo_inventario')
                                ->ignore($record),
                        ];
                    }),

                Forms\Components\TextInput::make('serie')
                    ->label('Número de Serie')
                    ->nullable()
                    ->maxLength(255)
                    ->rules(function ($record) {
                        return [
                            'nullable',
                            'max:255',
                            Rule::unique('product_units', 'serie')
                                ->ignore($record),
                        ];
                    }),

                Forms\Components\Select::make('estado')
                    ->label('Estado')
                    ->required()
                    ->live()
                    ->options([
                        'disponible' => 'Disponible',
                        'dañado' => 'Dañado',
                    ])
                    ->default('disponible'),

                Forms\Components\Textarea::make('descripcion_averia')
                    ->columnSpanFull()
                    ->required(fn(Get $get) => $get('estado') === 'dañado')
                    ->hidden(fn(Get $get) => $get('estado') !== 'dañado'),

                Forms\Components\TextInput::make('descripcion_lugar')
                    ->label('Descripción del Lugar')
                    ->nullable()
                    ->maxLength(255)
                    ->rules(['nullable', 'min:5', 'max:255']),

                Forms\Components\TextInput::make('funcionario_responsable')
                    ->label('Funcionario Responsable')
                    ->nullable()
                    ->maxLength(255)
                    ->rules(['min:5', 'max:255']),

                Forms\Components\DatePicker::make('fecha_asignacion')
                    ->label('Fecha de Asignación')
                    ->nullable()
                    ->displayFormat('Y-m-d')
                    ->weekStartsOnMonday()
                    ->rules(['nullable', 'date', 'before_or_equal:today', 'after_or_equal:01-01-1900']),
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
                    })
                    ->tooltip(fn($record) => in_array($record->estado, ['reservado', 'prestado']) ? 'Los productos que se encuentren en estado reservado o prestado no pueden ser eliminado ni editados' : null),

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
                    ]),
                Tables\Filters\TrashedFilter::make(),
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
                Tables\Actions\EditAction::make()
                    ->before(function ($record) {})
                    ->disabled(fn($record) => in_array($record->estado, ['reservado', 'prestado'])),
                Tables\Actions\DeleteAction::make()
                    ->after(function () {

                        $parentProduct = $this->getOwnerRecord(); // Obtiene el modelo padre

                        $parentProduct->cantidad -= 1;
                        $parentProduct->save();
                    })
                    ->disabled(fn($record) => in_array($record->estado, ['reservado', 'prestado'])),
                Tables\Actions\RestoreAction::make(),
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
