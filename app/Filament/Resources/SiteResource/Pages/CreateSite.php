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

    protected function handleRecordCreation(array $data): Model
    {
        $slug = $data['page_slug'];
        unset($data['page_slug']);

        return DB::connection('mysql_web')->transaction(function () use ($data, $slug): Site {
            $page = Page::query()->create([
                'slug' => $slug,
                'title_zh_tw' => $data['name_zh_tw'],
                'title_en' => $data['name_en'],
                'nav_group' => 'sites',
                'nav_order' => ((int) Page::query()->where('nav_group', 'sites')->max('nav_order')) + 1,
            ]);

            return Site::query()->create($data + ['page_id' => $page->getKey()]);
        });
    }

    protected function getRedirectUrl(): string
    {
        return PageResource::getUrl('edit', ['record' => $this->record->page_id]);
    }
}
