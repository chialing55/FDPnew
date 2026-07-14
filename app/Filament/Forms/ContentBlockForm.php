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
                        ->label('顯示於前台')
                        ->default(true),
                ]),
                Tabs::make('內容語系')->tabs([
                    Tabs\Tab::make('中文')->schema([Forms\Components\TextInput::make('title_zh_tw')->label('區塊標題')]),
                    Tabs\Tab::make('English')->schema([Forms\Components\TextInput::make('title_en')->label('Section title')]),
                ]),
                Repeater::make('items')
                    ->label('章節內容')
                    ->relationship('items')
                    ->orderColumn('sort_order')
                    ->reorderable()
                    ->collapsible()
                    ->addActionLabel('新增內容項目')
                    ->itemLabel(fn (array $state): string => ($state['type'] ?? 'text') === 'text' ? '文字' : '動態元件')
                    ->schema([
                        Forms\Components\Select::make('type')->label('內容類型')->options([
                            'text' => '文字',
                            'component' => '動態元件',
                        ])->default('text')->live()->required(),
                        Forms\Components\Toggle::make('is_public')
                            ->label('顯示於前台')
                            ->default(true)
                            ->columnSpanFull(),
                        Tabs::make('文字內容')->visible(fn (Forms\Get $get): bool => $get('type') === 'text')->tabs([
                            Tabs\Tab::make('中文')->schema([HtmlContentEditor::make('body_zh_tw')->label('內容')]),
                            Tabs\Tab::make('English')->schema([HtmlContentEditor::make('body_en')->label('Content')]),
                        ])->columnSpanFull(),
                        Forms\Components\TextInput::make('component')
                            ->label('Livewire 元件名稱')
                            ->helperText('例如 web.fushan-climate-chart；僅限系統已建立的元件。')
                            ->visible(fn (Forms\Get $get): bool => $get('type') === 'component')
                            ->required(fn (Forms\Get $get): bool => $get('type') === 'component'),
                    ])
                    ->columns(2),
            ]);
    }
}
