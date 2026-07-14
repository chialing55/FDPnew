<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 此入口只建立「關於我們」頁面；樣區與研究主題各自由專屬入口建立並自動分類。
        $data['nav_group'] = 'about';

        $uploadedHero = $data['uploaded_hero_image'] ?? null;
        unset($data['uploaded_hero_image']);

        if (filled($uploadedHero)) {
            $data['hero_image'] = $uploadedHero;
        }

        return $data;
    }
}
