<?php

namespace App\Filament\ChangYang\Resources\ChangYangPublicationResource\Pages;

use App\Filament\ChangYang\Resources\ChangYangPublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChangYangPublication extends EditRecord
{
    protected static string $resource = ChangYangPublicationResource::class;

    public function getBreadcrumbs(): array
    {
        return [ChangYangPublicationResource::listUrl() => '學術產出', '編輯學術產出'];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('回學術產出列表')
                ->icon('heroicon-o-arrow-left')
                ->url(ChangYangPublicationResource::listUrl()),
            Actions\DeleteAction::make()
                ->label('刪除')
                ->requiresConfirmation()
                ->successRedirectUrl(ChangYangPublicationResource::listUrl()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ChangYangPublicationResource::listUrl();
    }
}
