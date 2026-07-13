<?php
namespace App\Filament\Resources\NewsResource\Pages;
use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;
    public function getTitle(): string { return '編輯最新消息：' . $this->record->title_zh_tw; }
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
