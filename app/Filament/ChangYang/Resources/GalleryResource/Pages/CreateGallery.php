<?php

namespace App\Filament\ChangYang\Resources\GalleryResource\Pages;

use App\Filament\ChangYang\Resources\GalleryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    public function getBreadcrumbs(): array
    {
        return [GalleryResource::listUrl() => '相簿列表', '新增相簿'];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')->label('回相簿列表')->icon('heroicon-o-arrow-left')->url(GalleryResource::listUrl()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return GalleryResource::listUrl();
    }
}
