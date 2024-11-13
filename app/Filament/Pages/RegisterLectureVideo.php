<?php

namespace App\Filament\Pages;

use App\Filament\Resources\LectureResource;
use Filament\Actions;
use Filament\Pages\Page;

class RegisterLectureVideo extends Page
{
  protected static ?string $slug = '/lectures/{id}/register-video';

  protected static string $view = 'filament.pages.lecture-register-video';

  protected static bool $shouldRegisterNavigation = false;

  public function mount($id)
  {
    dd($id);
  }
}
