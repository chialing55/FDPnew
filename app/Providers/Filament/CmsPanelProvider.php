<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\NavigationGroup;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;


class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cms')
            ->path('cms')
            ->authGuard('web')
            ->brandName('森林動態樣區研究成果平台・後台') 
            ->brandLogo(asset('images/紅楠_葉_72_300.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/紅楠_葉_72_300.png'))
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make()->label('首頁')->icon('heroicon-o-home'),
                NavigationGroup::make()->label('動態樣區')->icon('heroicon-o-map'),
                NavigationGroup::make()->label('研究主題')->icon('heroicon-o-academic-cap'),
                NavigationGroup::make()->label('研究成果')->icon('heroicon-o-chart-bar-square'),
                NavigationGroup::make()->label('關於我們')->icon('heroicon-o-information-circle'),
            ])
            ->navigationItems([
                NavigationItem::make('回到前台首頁')
                    ->url(url('/'))           // 如果有 route('index') 也可以改成 ->url(route('index'))
                    ->icon('heroicon-o-home')
                    ->sort(99)
                    ->openUrlInNewTab(),     // 新分頁打開，比較安全
                NavigationItem::make('回資料管理系統')
                    ->url(url('/admin/choice'))
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->sort(100),
            ])
            ->colors([
                'primary' => Color::hex('#4f772d'),
            ])
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => view('filament.cms-theme')->render())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
