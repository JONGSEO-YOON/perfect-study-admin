<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
        Model::unguard();
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                    ->label('교실 관리')
                    ->icon('heroicon-m-academic-cap'),
                NavigationGroup::make()
                    ->label('문제 관리')
                    ->icon('heroicon-m-clipboard-document-list'),
                NavigationGroup::make()
                    ->label('자료실')
                    ->icon('heroicon-m-archive-box'),
                NavigationGroup::make()
                    ->label('설정')
                    ->icon('heroicon-m-cog-6-tooth'),
            ]);
        });

        if (file_exists(base_path('bootstrap/helpers.php'))) {
            require_once base_path('bootstrap/helpers.php');
        }
    }
}
