<?php

namespace App\Filament\ChangYang\Resources\ChangYangPageResource\Pages;

use App\Filament\ChangYang\Resources\ChangYangPageResource;
use Filament\Resources\Pages\ListRecords;

class ListChangYangPages extends ListRecords
{
    protected static string $resource = ChangYangPageResource::class;

    public function toggleTableReordering(): void
    {
        parent::toggleTableReordering();

        if (! $this->isTableReordering) {
            $this->redirect(ChangYangPageResource::getUrl('index'));
        }
    }
}
