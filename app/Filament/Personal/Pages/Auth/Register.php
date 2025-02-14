<?php

namespace App\Filament\Personal\Pages\Auth;


use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Position;
use Filament\Forms\Form;
use App\Models\Department;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;
use Filament\Events\Auth\Registered;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;

class Register extends BaseRegister
{
    protected ?string $maxWidth = '2xl';

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Wizard::make([
                            Wizard\Step::make('Principal')
                                ->schema([
                                    $this->getNameFormComponent(),
                                    $this->getEmailFormComponent(),
                                    $this->getPasswordFormComponent(),
                                    $this->getPasswordConfirmationFormComponent(),
                                ]),
                            Wizard\Step::make('Area')
                                ->schema([
                                    $this->getPositionFormComponent(),
                                    $this->getDepartmentFormComponent(),
                                ]),
                            Wizard\Step::make('Address')
                                ->schema([
                                    $this->getCountryFormComponent(),
                                    $this->getStateFormComponent(),
                                    $this->getCityFormComponent(),
                                    $this->getAddressFormComponent(),
                                    $this->getPostalCodeFormComponent(),
                                ]),
                        ]),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::pages/auth/register.form.name.label'))
            ->required()
            ->rules(['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/', 'min:4']) // Ejemplo: solo letras, números y espacios
            ->validationMessages([
                'regex' => 'El campo :attribute solo puede contener letras, números y espacios.',
                'min' => 'El campo :attribute debe tener al menos 4 caracteres.',
            ])
            ->maxLength(255)
            ->autofocus();
    }

    protected function getCountryFormComponent(): Component
    {
        return Select::make('country_id')
            ->label(__('Country'))
            ->live()
            ->options(function () {
                $data = Country::query()
                    ->pluck('name', 'id');

                return $data;
            })
            ->afterStateUpdated(function (Set $set) {
                $set('state_id', null);
                $set('city_id', null);
            })
            ->searchable()
            ->preload()
            ->default(48)
            ->required();
    }

    protected function getStateFormComponent(): Component
    {
        return Select::make('state_id')
            ->label(__('State'))
            ->options(function (Get $get) {
                $data = State::query()
                    ->where('country_id', $get('country_id'))
                    ->pluck('name', 'id');

                return $data;
            })
            ->live()
            ->searchable()
            ->preload()
            ->required();
    }

    protected function getCityFormComponent(): Component
    {
        return Select::make('city_id')
            ->label(__('City'))
            ->options(
                function (Get $get) {
                    return  City::query()
                        ->where('state_id', $get('state_id'))
                        ->pluck('name', 'id');
                }
            )
            ->live()
            ->searchable()
            ->preload()
            ->required();
    }

    protected function getAddressFormComponent(): Component
    {
        return TextInput::make('address')
            ->label(__('Address'))
            ->rules(['string', 'regex:/^[a-zA-Z0-9\s]+$/', 'min:4']) // Ejemplo: solo letras, números y espacios
            ->validationMessages([
                'regex' => 'El campo :attribute solo puede contener letras, números y espacios.',
                'min' => 'El campo :attribute debe tener al menos 4 caracteres.',
            ]);
    }

    protected function getPostalCodeFormComponent(): Component
    {
        return TextInput::make('postal_code')
            ->label(__('Postal Code'))
            ->rules(['numeric'])
            ->validationMessages([
                'numeric' => 'El campo :attribute solo puede contener números.',
            ]);
    }

    protected function getPositionFormComponent(): Component
    {
        return Select::make('position_id')
            ->label(__('Position'))
            ->options(fn() => Position::query()->pluck('nombre', 'id'))
            ->live()
            ->searchable()
            ->preload()
            ->required();
    }

    protected function getDepartmentFormComponent(): Component
    {
        return Select::make('department_id')
            ->label(__('Department'))
            ->options(fn() => Department::query()->pluck('nombre', 'id'))
            ->live()
            ->searchable()
            ->preload()
            ->required();
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $user = $this->wrapInDatabaseTransaction(function () {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeRegister($data);

            $this->callHook('beforeRegister');

            $user = $this->handleRegistration($data);

            $this->form->model($user)->saveRelationships();

            $this->callHook('afterRegister');

            // Se asigna el rol de panel_user para los usuarios que se registren
            $user->assignRole('panel_user');

            return $user;
        });

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }
}
