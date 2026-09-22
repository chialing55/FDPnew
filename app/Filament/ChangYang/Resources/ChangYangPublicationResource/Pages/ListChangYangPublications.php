<?php

namespace App\Filament\ChangYang\Resources\ChangYangPublicationResource\Pages;

use App\Filament\ChangYang\Resources\ChangYangPublicationResource;
use App\Filament\Actions\SyncZoteroPublicationsAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChangYangPublications extends ListRecords
{
    protected static string $resource = ChangYangPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SyncZoteroPublicationsAction::make(),
            Actions\CreateAction::make()->label('新增學術產出'),
        ];
    }
}
