<?php

namespace App\Filament\ChangYang\Resources\PersonCategoryResource\Pages;

use App\Filament\ChangYang\Resources\PersonCategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonCategories extends ListRecords
{
    protected static string $resource = PersonCategoryResource::class;

    protected static ?string $title = '人物類型';
}
