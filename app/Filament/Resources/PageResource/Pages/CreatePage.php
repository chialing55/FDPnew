<?php

namespace App\Filament\Resources\PageResource\Pages;

use App\Filament\Resources\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $uploadedHero = $data['uploaded_hero_image'] ?? null;
        unset($data['uploaded_hero_image']);

        if (filled($uploadedHero)) {
            $data['hero_image'] = $uploadedHero;
        }

        return $data;
    }
}
