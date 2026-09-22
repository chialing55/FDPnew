<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected bool $canManageChangyangSite = false;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['can_manage_changyang_site'] = $this->record->canManageChangyangSite();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->canManageChangyangSite = (bool) ($data['can_manage_changyang_site'] ?? false);
        unset($data['can_manage_changyang_site']);

        return $data;
    }

    protected function afterSave(): void
    {
        UserResource::syncChangyangPermission($this->record, $this->canManageChangyangSite);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
