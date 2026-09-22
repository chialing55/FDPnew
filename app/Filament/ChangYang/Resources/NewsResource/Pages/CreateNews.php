<?php

namespace App\Filament\ChangYang\Resources\NewsResource\Pages;

use App\Filament\ChangYang\Resources\NewsResource;
use App\Models\ChangYang\NewsItem;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;

    public function getBreadcrumbs(): array
    {
        return [NewsResource::listUrl() => '消息列表', '新增消息'];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')->label('回消息列表')->icon('heroicon-o-arrow-left')->url(NewsResource::listUrl()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return NewsResource::listUrl();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Keep existing manual positions, while placing each new message first.
        NewsItem::query()->increment('sort_order');
        $data['sort_order'] = 1;

        return $data;
    }
}
