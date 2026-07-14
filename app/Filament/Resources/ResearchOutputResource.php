<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResearchOutputResource\Pages;
use App\Filament\Forms\ContentBlockForm;
use App\Filament\Forms\ContentRelationForm;
use App\Filament\Forms\PageBasicFields;
use App\Filament\Forms\HeroImageField;
use App\Models\Web\ResearchOutput;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ResearchOutputResource extends Resource
{
    protected static ?string $model = ResearchOutput::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = '研究成果';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = '研究成果';
    protected static ?string $modelLabel = '成果';
    protected static ?string $pluralModelLabel = '研究成果';

    public static function form(Form $form): Form
    {
        if ($form->getOperation() === 'create') {
            return $form->schema([
                Forms\Components\Section::make('基本資料')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        ...PageBasicFields::make(
                            urlField: Forms\Components\TextInput::make('slug')
                                ->label('頁面網址')
                                ->prefix(url('/results') . '/')
                                ->required()
                                ->maxLength(150)
                                ->unique()
                                ->helperText('只需填最後一段，例如 fushan-forest-composition。頁面公開後請勿隨意修改。'),
                            titleEnRequired: false,
                        ),
                    ]),
                ]),
            ]);
        }

        return $form->schema([
            Tabs::make('成果設定')->tabs([
                Tabs\Tab::make('基本資料')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            ...PageBasicFields::make(
                                urlField: Forms\Components\TextInput::make('slug')
                                ->label('頁面網址')->prefix(url('/results') . '/')
                                ->required()->maxLength(150)->unique(ignoreRecord: true)
                                ->helperText('例如 fushan-forest-composition；不需輸入 results/。'),
                                titleEnRequired: false,
                            ),
                        ]),
                    ]),

                Tabs\Tab::make('關聯設定')
                    ->icon('heroicon-o-link')
                    ->schema([
                        ...ContentRelationForm::fields(),
                    ])->columns(2),

                Tabs\Tab::make('導覽與 Hero')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        Forms\Components\Section::make('導覽設定')
                            ->description('單筆研究成果固定歸屬研究成果頁；頁面基本資料與共用 Hero 可由此進入設定。')
                            ->schema([
                                Forms\Components\Placeholder::make('results_navigation_settings')
                                    ->label('研究成果頁')
                                    ->content(fn (): HtmlString => new HtmlString(
                                        '<a class="font-semibold text-primary-600 hover:underline" href="'
                                        . e(PageResource::getUrl('edit', [
                                            'record' => \App\Models\Web\Page::query()->where('slug', 'results')->firstOrFail(),
                                        ]))
                                        . '">編輯研究成果頁基本資料與 Hero</a>'
                                    )),
                            ]),
                        Forms\Components\Section::make('選擇研究成果 Hero')
                            ->description('選擇此研究成果專用的 Hero；未選擇時沿用研究成果頁 Hero。')
                            ->schema([
                                HeroImageField::make()->columnSpanFull(),
                            ]),
                    ]),

                Tabs\Tab::make('頁面內容')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        Forms\Components\Placeholder::make('content_notice')
                            ->label('頁面內容說明')
                            ->content('成果頁面的內容全部由下方內容區塊組成；「研究計畫」與「文章發表」為前台固定區塊，不需在此重複建立。'),
                        ContentBlockForm::make()->columnSpanFull(),
                    ]),
            ])->persistTabInQueryString()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (ResearchOutput $record): string => static::getUrl('edit', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('title_zh_tw')
                    ->label('成果名稱')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('網址')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('sites.name_zh_tw')
                    ->label('樣區')->badge()->separator(', '),
                Tables\Columns\TextColumn::make('subjects.name_zh_tw')
                    ->label('研究主題')->badge()->separator(', '),
                Tables\Columns\IconColumn::make('is_active')->label('公開')->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make()->label('編輯內容'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResearchOutputs::route('/'),
            'create' => Pages\CreateResearchOutput::route('/create'),
            'edit' => Pages\EditResearchOutput::route('/{record}/edit'),
        ];
    }
}
