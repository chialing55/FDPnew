<?php
namespace App\Filament\Resources\PublicationResource\Pages;
use App\Filament\Resources\PublicationResource;
use App\Filament\Support\CmsListPageNavigation;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

    public function getBreadcrumbs(): array
    {
        return [CmsListPageNavigation::tabUrl('publications') => '學術產出', '新增學術產出'];
    }

    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back')->label('回學術產出列表')->icon('heroicon-o-arrow-left')
            ->url(CmsListPageNavigation::tabUrl('publications'))];
    }

    protected function getRedirectUrl(): string
    {
        return PublicationResource::getUrl('edit', ['record' => $this->record]);
    }
}
