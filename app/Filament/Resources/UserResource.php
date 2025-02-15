<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\City;
use App\Models\User;
use Filament\Tables;
use App\Models\State;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Gestión de usuarios';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    // protected static ?string $navigationLabel = 'Usuarios';

    // Con este metodo se sobreescribe el label que usa Filament para establecer nombres del recurso a traves de toda la UI
    public static function getModelLabel(): string
    {
        return __('User');
    }

    // Con este metodo se sobreescribe el label que usa Filament para establecer nombres del recurso a traves de toda la UI
    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Personal Info')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electronico')
                            ->disabled(fn($record) => $record !== null)
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->hiddenOn('edit')
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Additional Info')
                    ->schema([
                        Forms\Components\Select::make('position_id')
                            ->relationship(name: 'position', titleAttribute: 'nombre')
                            ->label('Position')
                            ->required(),

                        Forms\Components\Select::make('department_id')
                            ->relationship(name: 'department', titleAttribute: 'nombre')
                            ->label('Area')
                            ->required(),

                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->columns(2),

                Section::make('Address Info')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->relationship(name: 'country', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('state_id', null);
                                $set('city_id', null);
                            })
                            ->label('País')
                            ->required(),

                        Forms\Components\Select::make('state_id')
                            ->options(fn(Get $get): Collection => State::query()
                                ->where('country_id', $get('country_id'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('city_id', null);
                            })
                            ->label('Departamento')
                            ->required(),

                        Forms\Components\Select::make('city_id')
                            ->options(
                                function (Get $get) {
                                    return $get('state_id')
                                        ? City::query()
                                        ->where('state_id', $get('state_id'))
                                        ->pluck('name', 'id')
                                        : collect();
                                }
                            )
                            ->searchable()
                            ->label('Ciudad')
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('address')
                            ->label('Dirección')
                            ->rules(['string', 'regex:/^[a-zA-Z0-9\s]+$/', 'min:4']) // Ejemplo: solo letras, números y espacios
                            ->validationMessages([
                                'regex' => 'El campo :attribute solo puede contener letras, números y espacios.',
                                'min' => 'El campo :attribute debe tener al menos 4 caracteres.',
                            ]),

                        Forms\Components\TextInput::make('postal_code')
                            ->label('Codigo Postal')
                            ->rules(['numeric'])
                            ->validationMessages([
                                'numeric' => 'El campo :attribute solo puede contener números.',
                            ]),


                    ])
                    ->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('position.nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('postal_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
