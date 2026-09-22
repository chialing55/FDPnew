<?php

namespace App\Filament\ChangYang\Support;

use App\Filament\ChangYang\Resources\ChangYangPageResource;
use App\Models\ChangYang\Page;

class ChangYangPageLink
{
    public static function tab(string $slug): string
    {
        return ChangYangPageResource::getUrl('edit', [
            'record' => Page::query()->where('slug', $slug)->firstOrFail(),
            'tab' => "-{$slug}-tab",
        ], panel: 'changyang-admin');
    }
}
