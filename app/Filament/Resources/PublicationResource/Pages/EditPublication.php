<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use App\Filament\Support\CmsListPageNavigation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

    public function getBreadcrumbs(): array
    {
        return [CmsListPageNavigation::tabUrl('publications') => '學術產出', '編輯學術產出'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')->label('回學術產出列表')->icon('heroicon-o-arrow-left')
                ->url(CmsListPageNavigation::tabUrl('publications')),
            Actions\DeleteAction::make()
                ->label('刪除')
                ->requiresConfirmation(),
        ];
    }
}
