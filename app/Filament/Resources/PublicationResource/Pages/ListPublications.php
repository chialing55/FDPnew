<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use App\Filament\Support\CmsListPageNavigation;
use Filament\Resources\Pages\ListRecords;

class ListPublications extends ListRecords
{
    protected static string $resource = PublicationResource::class;

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->redirect(CmsListPageNavigation::tabUrl('publications'));
    }
}
