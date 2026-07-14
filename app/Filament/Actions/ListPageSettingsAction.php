<?php

namespace App\Filament\Actions;

use App\Filament\Resources\PageResource;
use App\Models\Web\Page;
use Filament\Actions\Action;

class ListPageSettingsAction
{
    public static function make(string $slug, string $label): Action
    {
        return Action::make('listPageSettings')
            ->label($label . '基本資料與 Hero')
            ->icon('heroicon-o-photo')
            ->color('gray')
            ->url(fn (): string => PageResource::getUrl('edit', [
                'record' => Page::query()->where('slug', $slug)->firstOrFail(),
            ]));
    }
}
