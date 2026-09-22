<?php

namespace App\Filament\Support;

use App\Filament\Resources\PageResource;
use App\Models\Web\Page;
use Filament\Navigation\NavigationItem;

class CmsListPageNavigation
{
    public static function tabUrl(string $slug): string
    {
        return PageResource::getUrl('edit', [
            'record' => Page::query()->where('slug', $slug)->firstOrFail(),
            'tab' => "-{$slug}-tab",
        ]);
    }

    public static function make(string $slug, string $label, string $icon, int $sort): NavigationItem
    {
        return NavigationItem::make($label)
            ->group('研究成果')
            ->icon($icon)
            ->sort($sort)
            ->url(fn (): string => static::tabUrl($slug))
            ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.pages.edit')
                && Page::query()->whereKey(request()->route('record'))->where('slug', $slug)->exists());
    }
}
