<?php
namespace App\Filament\Resources\PublicationResource\Pages;
use App\Filament\Resources\PublicationResource;
use Filament\Resources\Pages\CreateRecord;
class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    protected function getRedirectUrl(): string
    {
        return PublicationResource::getUrl('edit', ['record' => $this->record]);
    }
}
