<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;
    protected ?string $subheading = '先從列表選擇一筆研究計畫；點擊整列或「編輯內容」即可進入編輯頁面。';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('新增研究計畫'),
        ];
    }
}
