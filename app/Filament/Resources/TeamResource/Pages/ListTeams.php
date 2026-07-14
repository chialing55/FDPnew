<?php

namespace App\Filament\Resources\TeamResource\Pages;

use App\Filament\Actions\ListPageSettingsAction;
use App\Filament\Resources\TeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeams extends ListRecords
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ListPageSettingsAction::make('about/team', '研究團隊頁'),
            Actions\CreateAction::make(),
        ];
    }
}
