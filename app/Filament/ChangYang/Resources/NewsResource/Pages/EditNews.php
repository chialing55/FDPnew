<?php

namespace App\Filament\ChangYang\Resources\NewsResource\Pages;

use App\Filament\ChangYang\Resources\NewsResource;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    public function getBreadcrumbs(): array
    {
        return [NewsResource::listUrl() => '消息列表', '編輯消息'];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')->label('回消息列表')->icon('heroicon-o-arrow-left')->url(NewsResource::listUrl()),
            \Filament\Actions\DeleteAction::make()->successRedirectUrl(NewsResource::listUrl()),
        ];
    }

    protected function configureDeleteAction(\Filament\Actions\DeleteAction $action): void
    {
        $action->authorize(NewsResource::canDelete($this->getRecord()))
            ->successRedirectUrl(NewsResource::listUrl());
    }

    protected function getRedirectUrl(): string
    {
        return NewsResource::listUrl();
    }
}
