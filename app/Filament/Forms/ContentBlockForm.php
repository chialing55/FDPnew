<?php

namespace App\Filament\Forms;

use App\Forms\Components\HtmlContentEditor;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;

class ContentBlockForm
{
    public static function make(string $label = '內容區塊'): Repeater
    {
        return Repeater::make('contentBlocks')
            ->label($label)
            ->relationship('contentBlocks')
            ->orderColumn('sort_order')
            ->addActionLabel('新增內容區塊')
            ->reorderable()
            ->collapsible()
            ->collapsed()
            ->cloneable()
            ->itemLabel(fn (array $state): string => $state['title_zh_tw'] ?? '未命名區塊')
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('sort_order')
                        ->label('顯示順序')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_public')
                        ->label('發布到前台')
                        ->default(true),
                ]),
                Tabs::make('內容語系')->tabs([
                    Tabs\Tab::make('中文')->schema([
                        Forms\Components\TextInput::make('title_zh_tw')->label('區塊標題'),
                        HtmlContentEditor::make('body_zh_tw')->label('內容'),
                    ]),
                    Tabs\Tab::make('English')->schema([
                        Forms\Components\TextInput::make('title_en')->label('Section title'),
                        HtmlContentEditor::make('body_en')->label('Content'),
                    ]),
                ]),
            ]);
    }
}
