<?php

namespace App\Filament\Resources\ResearchOutputResource\Pages;

use App\Filament\Actions\ListPageSettingsAction;
use App\Filament\Resources\ResearchOutputResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResearchOutputs extends ListRecords
{
    protected static string $resource = ResearchOutputResource::class;

    protected ?string $subheading = '先從列表選擇一筆成果；點擊整列或「編輯內容」即可進入編輯頁面。';

    protected function getHeaderActions(): array
    {
        return [
            ListPageSettingsAction::make('results', '研究成果頁'),
            Actions\CreateAction::make()->label('新增研究成果'),
        ];
    }
}
