<?php

namespace App\Filament\Resources\ResearchOutputResource\Pages;

use App\Filament\Resources\ResearchOutputResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResearchOutput extends EditRecord
{
    protected static string $resource = ResearchOutputResource::class;

    public function getTitle(): string
    {
        return '編輯研究成果：' . ($this->record->title_zh_tw ?: $this->record->slug);
    }

    public function getBreadcrumb(): string
    {
        return $this->record->title_zh_tw ?: $this->record->slug;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
