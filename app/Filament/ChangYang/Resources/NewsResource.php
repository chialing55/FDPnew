<?php

namespace App\Filament\ChangYang\Resources;

use App\Models\ChangYang\NewsItem;
use App\Filament\ChangYang\Support\ChangYangPageLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

class NewsResource extends Resource
{
    protected static ?string $model = NewsItem::class;

    protected static ?string $slug = 'news';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = '消息';

    public static function listUrl(): string
    {
        return ChangYangPageLink::tab('news');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('category_year')->label('年份')->numeric()->integer()->minValue(1900)->maxValue(2200)->default(now()->year)->required(),
                Forms\Components\Select::make('category_month')->label('月份')->options(array_combine(range(1, 12), range(1, 12)))->default(now()->month)->required(),
            ]),
            \App\Forms\Components\PersonContentEditor::make('content_html')->label('消息內容')->required()->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')->label('公開')->default(true),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'create' => NewsResource\Pages\CreateNews::route('/create'),
            'edit' => NewsResource\Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
