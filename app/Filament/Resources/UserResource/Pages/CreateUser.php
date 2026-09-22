<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected bool $canManageChangyangSite = false;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->canManageChangyangSite = (bool) ($data['can_manage_changyang_site'] ?? false);
        unset($data['can_manage_changyang_site']);

        return $data;
    }

    protected function afterCreate(): void
    {
        UserResource::syncChangyangPermission($this->record, $this->canManageChangyangSite);
    }
}
