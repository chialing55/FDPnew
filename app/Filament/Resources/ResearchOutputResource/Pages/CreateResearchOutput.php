<?php

namespace App\Filament\Resources\ResearchOutputResource\Pages;

use App\Filament\Resources\ResearchOutputResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResearchOutput extends CreateRecord
{
    protected static string $resource = ResearchOutputResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['title_en'] ??= '';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return ResearchOutputResource::getUrl('edit', ['record' => $this->record]);
    }
}
