<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Setting;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TimePicker;
use App\Filament\Resources\SettingResource\Pages;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): ?string
    {
        return __('System Management');
    }

    // Con este metodo se sobreescribe el label que usa Filament para establecer nombres del recurso a traves de toda la UI
    public static function getModelLabel(): string
    {
        return __('Time Setting');
    }

    // Con este metodo se sobreescribe el label que usa Filament para establecer nombres del recurso a traves de toda la UI
    public static function getPluralModelLabel(): string
    {
        return __('Time Settings');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('dia')
                    ->label(__('Day'))
                    ->options([
                        'monday' => __('monday'),
                        'tuesday' => __('tuesday'),
                        'wednesday' => __('wednesday'),
                        'thursday' => __('thursday'),
                        'friday' => __('friday'),
                        'saturday' => __('saturday'),
                        'sunday' => __('sunday'),
                    ])
                    ->unique(ignoreRecord: true)
                    ->required(),

                Section::make(__('Service Hours'))
                    ->description(__('Define the times when the service is available'))
                    ->schema([
                        Section::make(__('Operating Hours'))
                            ->description(__('Set the times when the service is open'))
                            ->schema([
                                TimePicker::make('hora_apertura')
                                    ->label(__('Opening Time'))
                                    ->required()
                                    ->before('hora_cierre')
                                    ->seconds(false),
                                TimePicker::make('hora_cierre')
                                    ->label(__('Closing Time'))
                                    ->required()
                                    ->after('hora_apertura')
                                    ->seconds(false),
                            ])
                            ->columns(2),

                        Section::make(__('Request Submission Hours'))
                            ->description(__('Set the times when users can submit requests'))
                            ->schema([
                                TimePicker::make('hora_solicitudes_apertura')
                                    ->label(__('Request Opening Time'))
                                    ->before('hora_solicitudes_cierre')
                                    ->afterOrEqual('hora_apertura')
                                    ->beforeOrEqual('hora_cierre')
                                    ->required()
                                    ->seconds(false),
                                TimePicker::make('hora_solicitudes_cierre')
                                    ->label(__('Request Closing Time'))
                                    ->after('hora_solicitudes_apertura')
                                    ->afterOrEqual('hora_apertura')
                                    ->beforeOrEqual('hora_cierre')
                                    ->required()
                                    ->seconds(false),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('dia')
                    ->formatStateUsing(fn(string $state): string => __(($state))),
                Tables\Columns\TextColumn::make('hora_apertura')
                    ->label(__('Opening Time')),
                Tables\Columns\TextColumn::make('hora_cierre')
                    ->label(__('Closing Time')),
                Tables\Columns\TextColumn::make('hora_solicitudes_apertura')
                    ->label(__('Request Opening Time')),
                Tables\Columns\TextColumn::make('hora_solicitudes_cierre')
                    ->label(__('Request Closing Time')),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
