<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Forms\Components\HtmlContentEditor;
use App\Models\Web\News;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;
    protected static ?string $navigationGroup = '關於我們';
    protected static ?string $navigationLabel = '最新消息';
    protected static ?string $modelLabel = '最新消息';
    protected static ?string $pluralModelLabel = '最新消息';
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('消息設定')->tabs([
                Tabs\Tab::make('基本資料')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title_zh_tw')->label('中文標題')->required()->maxLength(255),
                        Forms\Components\TextInput::make('title_en')->label('英文標題')->maxLength(255),
                        Forms\Components\DatePicker::make('publish_date')->label('發布日期')->required()->default(now())->native(false),
                        Forms\Components\Toggle::make('is_public')->label('發布到前台')->default(true),
                        Forms\Components\Toggle::make('is_featured')->label('設為精選消息')->default(false),
                    ]),
                    Forms\Components\FileUpload::make('cover_image')->label('封面圖片')
                        ->disk('public')->directory('news')->visibility('public')->image()->imageEditor(),
                    Forms\Components\TextInput::make('external_url')->label('外部連結')->url()
                        ->helperText('填寫後，首頁點擊此消息會直接前往外部網站；留空則進入本站消息內容頁。'),
                ]),
                Tabs\Tab::make('消息內容')->schema([
                    Tabs::make('內容語系')->tabs([
                        Tabs\Tab::make('中文')->schema([
                            HtmlContentEditor::make('content_zh_tw')->label('中文內容'),
                        ]),
                        Tabs\Tab::make('English')->schema([
                            HtmlContentEditor::make('content_en')->label('English content'),
                        ]),
                    ]),
                ]),
            ])->persistTabInQueryString()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->recordUrl(fn (News $record): string => static::getUrl('edit', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('publish_date')->label('發布日期')->date('Y-m-d')->sortable(),
                Tables\Columns\TextColumn::make('title_zh_tw')->label('消息標題')->searchable()->sortable()->wrap(),
                Tables\Columns\IconColumn::make('is_featured')->label('精選')->boolean(),
                Tables\Columns\IconColumn::make('is_public')->label('公開')->boolean(),
            ])->defaultSort('publish_date', 'desc')
            ->actions([Tables\Actions\EditAction::make()->label('編輯內容')])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListNews::route('/'), 'create' => Pages\CreateNews::route('/create'), 'edit' => Pages\EditNews::route('/{record}/edit')];
    }
}
