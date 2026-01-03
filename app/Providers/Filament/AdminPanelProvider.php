<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Login;
use App\Http\Middleware\StudentBlockMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\NavigationGroup;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Enums\MaxWidth;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([

                'primary' => [
                    100 => '211, 251, 251',
                    200 => '168, 244, 248',
                    300 => '121, 221, 235',
                    400 => '85, 191, 216',
                    500 => '37, 150, 190',
                    600 => '27, 118, 163',
                    700 => '18, 89, 136',
                    800 => '11, 63, 110',
                    900 => '7, 45, 91'
                ],
            ])
            ->plugins(
                [
                    // \Hasnayeen\Themes\ThemesPlugin::make(),
                ]
            )
            ->brandLogo(asset('logo.png'))
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('교실 관리'),
                NavigationGroup::make('출결'),
                NavigationGroup::make('문제 관리'),
                NavigationGroup::make('결제'),
                NavigationGroup::make('자료실'),
                NavigationGroup::make('설정'),
            ])
            ->font('Pretendard')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                // \Hasnayeen\Themes\Http\Middleware\SetTheme::class
            ])
            ->authMiddleware([
                Authenticate::class,
                StudentBlockMiddleware::class,
            ]);
    }
}
