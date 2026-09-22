<?php

namespace App\Filament\Resources\ResearchOutputResource\Pages;

use App\Filament\Actions\ListPageSettingsAction;
use App\Filament\Resources\ResearchOutputResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResearchOutputs extends ListRecords
{
    protected static string $resource = ResearchOutputResource::class;

    protected ?string $subheading = '目前前台暫停顯示研究成果；既有成果仍保留，可從列表選擇並編輯。';

    public function getTitle(): string
    {
        return '研究成果（暫停顯示）';
    }

    protected function getHeaderActions(): array
    {
        return [
            ListPageSettingsAction::make('results', '研究成果頁'),
            Actions\CreateAction::make()->label('新增研究成果'),
        ];
    }
}
