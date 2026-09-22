<?php

namespace App\Filament\ChangYang\Resources\PersonResource\Pages;

use App\Filament\ChangYang\Resources\PersonResource;
use Filament\Resources\Pages\EditRecord;

class EditPerson extends EditRecord
{
    protected static string $resource = PersonResource::class;

    public function getTitle(): string
    {
        return '編輯人物：'.$this->record->name;
    }

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
            $this->record->name,
        ];
    }

    protected function getRedirectUrl(): string
    {
        return PersonResource::getPeopleListUrl();
    }
}
