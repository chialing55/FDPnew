<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
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

    protected function afterSave(): void
    {
        $record = $this->record;
        $data = $this->data;

        // 只 sync 關聯
        $record->sites()->sync($data['sites'] ?? []);
        $record->subjects()->sync($data['subjects'] ?? []);
    }
}
