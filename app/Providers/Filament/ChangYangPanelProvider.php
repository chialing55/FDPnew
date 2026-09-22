<?php

namespace App\Providers\Filament;

use App\Filament\ChangYang\Pages\Dashboard;
use App\Filament\Support\AdminPanelDefaults;
use App\Http\Middleware\EnsureUserIsApproved;
use App\Http\Middleware\RedirectChangYangToSharedLogin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ChangYangPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return AdminPanelDefaults::configure($panel)
            ->id('changyang-admin')
            ->path('changyang-admin')
            ->authGuard('web')
            ->brandName(new HtmlString('<span>張楊家豪個人網站管理</span>'))
            ->favicon(asset('images/紅楠_葉_72_300.png'))
            ->colors(['primary' => Color::hex('#75685d')])
            ->discoverResources(in: app_path('Filament/ChangYang/Resources'), for: 'App\\Filament\\ChangYang\\Resources')
            ->pages([Dashboard::class])
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => view('filament.changyang-theme')->render())
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
            ])
            ->authMiddleware([
                RedirectChangYangToSharedLogin::class,
                EnsureUserIsApproved::class,
            ]);
    }
}
