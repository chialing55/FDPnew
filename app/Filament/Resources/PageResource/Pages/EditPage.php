<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public function getTitle(): string
    {
        $name = $this->record->site?->name_zh_tw ?: $this->record->title_zh_tw;

        return $this->record->nav_group === 'sites'
            ? '編輯動態樣區：' . $name
            : '編輯頁面：' . $name;
    }

    public function getBreadcrumb(): string
    {
        return $this->record->site?->name_zh_tw ?: $this->record->title_zh_tw;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('preview')
                ->label('預覽前台')
                ->icon('heroicon-o-eye')
                ->url(fn () => url('/' . ltrim($this->record->slug, '/')))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $uploadedHero = $data['uploaded_hero_image'] ?? null;
        unset($data['uploaded_hero_image']);

        if (filled($uploadedHero)) {
            $data['hero_image'] = $uploadedHero;
        }

        return $data;
    }
}
