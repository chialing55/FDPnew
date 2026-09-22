<?php

namespace App\Filament\Resources\ContentBlockResource\Pages;

use App\Filament\Resources\ContentBlockResource;
use App\Filament\Actions\ViewPublicPageAction;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentBlock extends EditRecord
{
    protected static string $resource = ContentBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewPublicPageAction::make(fn () => ContentBlockResource::getFrontendUrl($this->record))
                ->visible(fn () => filled(ContentBlockResource::getFrontendUrl($this->record))),
            Actions\DeleteAction::make(),
        ];
    }
}
