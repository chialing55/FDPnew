<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = '內容管理總覽';
    protected static ?string $navigationLabel = '總覽';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.dashboard';
}
