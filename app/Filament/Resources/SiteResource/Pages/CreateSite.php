<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\PageResource;
use App\Filament\Resources\SiteResource;
use App\Models\Web\Page;
use App\Models\Web\Site;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateSite extends CreateRecord
{
    protected static string $resource = SiteResource::class;

    public function getTitle(): string
    {
        return '新增動態樣區';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $tail = ltrim($data['page_slug'], '/');
        $slug = str_starts_with($tail, 'sites/') ? $tail : 'sites/' . $tail;
        unset($data['page_slug']);

        return DB::connection('mysql_web')->transaction(function () use ($data, $slug): Site {
            $sortOrder = ((int) Site::query()->max('sort_order')) + 1;

            $page = Page::query()->create([
                'slug' => $slug,
                'title_zh_tw' => $data['name_zh_tw'],
                'title_en' => $data['name_en'],
                'nav_group' => 'sites',
                'nav_order' => $sortOrder,
            ]);

            return Site::query()->create($data + [
                'page_id' => $page->getKey(),
                'sort_order' => $sortOrder,
            ]);
        });
    }

    protected function getRedirectUrl(): string
    {
        return PageResource::getUrl('edit', ['record' => $this->record->page_id]);
    }
}
