<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        $this->getLoginFormComponent(),
        $this->getPasswordFormComponent(),
        $this->getRememberFormComponent(),
      ])
      ->statePath('data');
  }

  protected function getLoginFormComponent(): Component
  {
    return TextInput::make('username')
      ->label('계정')
      ->required()
      ->autocomplete()
      ->autofocus()
      ->extraInputAttributes(['tabindex' => 1]);
  }

  protected function getCredentialsFromFormData(array $data): array
  {
    return [
      'username' => $data['username'],
      'password' => $data['password'],
    ];
  }

  protected function throwFailureValidationException(): never
  {
    throw ValidationException::withMessages([
      'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
    ]);
  }
}
