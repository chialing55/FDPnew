<?php

namespace App\Filament\ChangYang\Resources\PersonResource\Pages;

use App\Filament\ChangYang\Resources\PersonResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePerson extends CreateRecord
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('backToPeople')
                ->label('回人員列表')
                ->icon('heroicon-o-arrow-left')
                ->url(PersonResource::getPeopleListUrl()),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return [
            PersonResource::getPeopleListUrl() => '人員列表',
            '新增人物',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PersonResource::getPeopleListUrl();
    }
}
