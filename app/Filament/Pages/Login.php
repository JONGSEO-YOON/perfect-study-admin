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

    // 일반 강사는 다른 페이지로 리다이렉트 (예: 학생 목록)
    if ($user && $user->role === 'general') {
      return '/admin/students';
    }

    // 관리자, 매니저는 대시보드로
    if ($user && in_array($user->role, ['root_admin', 'admin', 'manager'])) {
      return '/admin/dashboard';
    }

    // 기본값
    return '/admin';
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
