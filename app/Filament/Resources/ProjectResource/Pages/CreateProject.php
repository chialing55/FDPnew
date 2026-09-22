<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use App\Filament\Support\CmsListPageNavigation;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function getBreadcrumbs(): array
    {
        return [CmsListPageNavigation::tabUrl('projects') => '研究計畫', '新增研究計畫'];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back')->label('回研究計畫列表')->icon('heroicon-o-arrow-left')
            ->url(CmsListPageNavigation::tabUrl('projects'))];
    }

    protected function getRedirectUrl(): string
    {
        return ProjectResource::getUrl('edit', ['record' => $this->record]);
    }
}
