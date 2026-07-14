<?php

namespace App\Filament\Forms;

use Filament\Forms;
use Filament\Forms\Components\Component;

class PageBasicFields
{
    /**
     * @return array<int, Component>
     */
    public static function make(
        ?Component $urlField = null,
        ?Component $visibilityField = null,
        string $titleZhTwField = 'title_zh_tw',
        string $titleEnField = 'title_en',
        string $titleZhTwLabel = '中文標題',
        string $titleEnLabel = '英文標題',
        bool $titleEnRequired = true,
    ): array {
        return array_values(array_filter([
            Forms\Components\TextInput::make($titleZhTwField)
                ->label($titleZhTwLabel)
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make($titleEnField)
                ->label($titleEnLabel)
                ->required($titleEnRequired)
                ->maxLength(255),
            $urlField ?? Forms\Components\Placeholder::make('page_url_placeholder')
                ->label('')
                ->content(''),
            $visibilityField ?? Forms\Components\Toggle::make('is_active')
                ->label('顯示於前台')
                ->default(true),
        ]));
    }
}
