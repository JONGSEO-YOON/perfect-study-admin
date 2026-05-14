<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Component;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

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
    $credentials = [
      'username' => $data['username'],
      'password' => $data['password'],
    ];

    // 서브도메인 학원 소속 사용자만 로그인 허용
    $academy = app()->bound('current_academy') ? app('current_academy') : null;
    if ($academy) {
      $credentials['academy_id'] = $academy->id;
    }

    return $credentials;
  }

  protected function throwFailureValidationException(): never
  {
    throw ValidationException::withMessages([
      'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
    ]);
  }

  protected function getRedirectUrl(): ?string
  {
    $user = Auth::user();

    // 상담실은 상담 관리로
    if ($user && $user->role === 'counselor') {
      return '/admin/counselings';
    }

    // 학원 관리자(root_admin, admin)만 대시보드로
    if ($user && in_array($user->role, ['root_admin', 'admin'])) {
      return '/admin/dashboard';
    }

    // 그 외(manager, general 등) 직원은 학생 목록으로
    return '/admin/students';
  }

  public function getHeading(): string
  {
    $academy = app()->bound('current_academy') ? app('current_academy') : null;
    if ($academy && $academy->login_welcome_message) {
      return $academy->login_welcome_message;
    }
    return __('filament-panels::pages/auth/login.heading');
  }
}
