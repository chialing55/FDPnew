<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Support\CmsListPageNavigation;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->redirect(CmsListPageNavigation::tabUrl('projects'));
    }
}
