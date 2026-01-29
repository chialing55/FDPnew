<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $data = $this->data;

        // 只需要 sync 關聯表
        $record->sites()->sync($data['sites'] ?? []);
        $record->subjects()->sync($data['subjects'] ?? []);
    }
}
