<?php

namespace App\Filament\Resources\ResearchOutputResource\Pages;

use App\Filament\Resources\ResearchOutputResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResearchOutputs extends ListRecords
{
    protected static string $resource = ResearchOutputResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
