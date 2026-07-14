<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use App\Models\Web\Site;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSites extends ListRecords
{
    protected static string $resource = SiteResource::class;

    protected ?string $subheading = '先從列表選擇一筆動態樣區；點擊整列或「編輯內容」即可進入新版編輯頁面。';

    public function reorderTable(array $order): void
    {
        parent::reorderTable($order);

        Site::query()->with('page')->whereIn('id', $order)->get()->each(
            fn (Site $site) => $site->page?->update(['nav_order' => $site->sort_order])
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('新增動態樣區'),
        ];
    }
}
