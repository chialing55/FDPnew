<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Support\CmsListPageNavigation;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    public function getTitle(): string
    {
        return '編輯研究計畫：' . $this->record->title_zh_tw;
    }

    public function getBreadcrumb(): string
    {
        return $this->record->title_zh_tw;
    }

    public function getBreadcrumbs(): array
    {
        return [CmsListPageNavigation::tabUrl('projects') => '研究計畫', $this->getBreadcrumb()];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back')->label('回研究計畫列表')->icon('heroicon-o-arrow-left')
            ->url(CmsListPageNavigation::tabUrl('projects'))];
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;

        // 只 sync 關聯
        $record->sites()->sync($data['sites'] ?? []);
        $record->subjects()->sync($data['subjects'] ?? []);
    }
}
