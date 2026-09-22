<?php

namespace App\Filament\ChangYang\Resources\PersonCategoryResource\Pages;

use App\Filament\ChangYang\Resources\PersonCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditPersonCategory extends EditRecord
{
    protected static string $resource = PersonCategoryResource::class;

    public function getTitle(): string
    {
        return '編輯人物類型：'.$this->record->title;
    }

    protected function getRedirectUrl(): string
    {
        return PersonCategoryResource::listUrl();
    }
}
