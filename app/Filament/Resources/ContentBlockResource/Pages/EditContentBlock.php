<?php

namespace App\Filament\Resources\ContentBlockResource\Pages;

use App\Filament\Resources\ContentBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContentBlock extends EditRecord
{
    protected static string $resource = ContentBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('預覽前台')
                ->icon('heroicon-o-eye')
                ->url(fn () => ContentBlockResource::getFrontendUrl($this->record))
                ->visible(fn () => filled(ContentBlockResource::getFrontendUrl($this->record)))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }
}
