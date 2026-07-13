<?php

namespace App\Filament\Forms;

use Filament\Forms;

class ContentRelationForm
{
    /**
     * @return array<int, Forms\Components\Select>
     */
    public static function fields(
        string $siteLabel = '樣區（可複選）',
        string $subjectLabel = '研究主題（可複選）',
    ): array {
        $sites = static::relationSelect('sites', $siteLabel);
        $subjects = static::relationSelect('subjects', $subjectLabel);

        return [$sites, $subjects];
    }

    protected static function relationSelect(string $relationship, string $label): Forms\Components\Select
    {
        return Forms\Components\Select::make($relationship)
            ->label($label)
            ->relationship($relationship, 'name_zh_tw')
            ->multiple()
            ->preload()
            ->searchable()
            ->native(false);
    }
}
