<?php
namespace App\Filament\Resources\NewsResource\Pages;
use App\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;
    protected ?string $subheading = '點擊列表中的消息即可進入編輯頁面。';
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()->label('新增最新消息')]; }
}
