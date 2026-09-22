<?php

namespace App\Filament\ChangYang\Pages;

use App\Filament\ChangYang\Resources\ChangYangPageResource;
use App\Models\ChangYang\Page;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.changyang.pages.dashboard';

    protected static ?string $title = '張楊家豪個人網站管理';

    protected static ?string $navigationLabel = '總覽';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected function getViewData(): array
    {
        $icons = [
            'home' => 'heroicon-o-home',
            'news' => 'heroicon-o-newspaper',
            'people' => 'heroicon-o-users',
            'research' => 'heroicon-o-beaker',
            'publications' => 'heroicon-o-book-open',
            'courses' => 'heroicon-o-academic-cap',
            'gallery' => 'heroicon-o-photo',
            'resources' => 'heroicon-o-link',
        ];

        return [
            'pages' => Page::query()
                ->orderBy('navigation_order')
                ->orderBy('id')
                ->get()
                ->map(fn (Page $page): array => [
                    'label' => $page->navigation_label ?: $page->title,
                    'title' => $page->title,
                    'icon' => $icons[$page->slug] ?? 'heroicon-o-document-text',
                    'is_active' => $page->is_active,
                    'edit_url' => ChangYangPageResource::getUrl('edit', ['record' => $page], panel: 'changyang-admin'),
                ])
                ->all(),
        ];
    }
}
