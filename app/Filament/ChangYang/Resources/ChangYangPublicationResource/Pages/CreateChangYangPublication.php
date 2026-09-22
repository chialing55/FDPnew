<?php

namespace App\Filament\ChangYang\Resources\ChangYangPublicationResource\Pages;

use App\Filament\ChangYang\Resources\ChangYangPublicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChangYangPublication extends CreateRecord
{
    protected static string $resource = ChangYangPublicationResource::class;

    public function getBreadcrumbs(): array
    {
        return [ChangYangPublicationResource::listUrl() => '學術產出', '新增學術產出'];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('回學術產出列表')
                ->icon('heroicon-o-arrow-left')
                ->url(ChangYangPublicationResource::listUrl()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return ChangYangPublicationResource::listUrl();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_changyang'] = true;

        return $data;
    }
}
