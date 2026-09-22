<?php

namespace App\Filament\ChangYang\Resources\GalleryResource\Pages;

use App\Filament\ChangYang\Resources\GalleryResource;
use Filament\Resources\Pages\EditRecord;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    public function getBreadcrumbs(): array
    {
        return [GalleryResource::listUrl() => '相簿列表', '編輯相簿'];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')->label('回相簿列表')->icon('heroicon-o-arrow-left')->url(GalleryResource::listUrl()),
            \Filament\Actions\DeleteAction::make()->successRedirectUrl(GalleryResource::listUrl()),
        ];
    }

    protected function configureDeleteAction(\Filament\Actions\DeleteAction $action): void
    {
        $action->authorize(GalleryResource::canDelete($this->getRecord()))
            ->successRedirectUrl(GalleryResource::listUrl());
    }

    protected function getRedirectUrl(): string
    {
        return GalleryResource::listUrl();
    }
}
