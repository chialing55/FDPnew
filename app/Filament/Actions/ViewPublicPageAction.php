<?php

namespace App\Filament\Actions;

use Closure;
use Filament\Actions\Action;

class ViewPublicPageAction
{
    /** @param string|Closure(): string $url */
    public static function make(string|Closure $url): Action
    {
        return Action::make('view-public-page')
            ->label('查看公開頁面')
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->url($url)
            ->openUrlInNewTab();
    }
}
