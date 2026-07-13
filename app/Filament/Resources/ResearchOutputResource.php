<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResearchOutputResource\Pages;
use App\Filament\Forms\ContentBlockForm;
use App\Filament\Forms\ContentRelationForm;
use App\Models\Web\ResearchOutput;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResearchOutputResource extends Resource
{
    protected static ?string $model = ResearchOutput::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = '研究成果';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationLabel = '成果列表';
    protected static ?string $modelLabel = '成果';
    protected static ?string $pluralModelLabel = '成果列表';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('成果設定')->tabs([
                Tabs\Tab::make('基本資料')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title_zh_tw')
                                ->label('中文標題')->required()->maxLength(255),
                            Forms\Components\TextInput::make('title_en')
                                ->label('英文標題')->maxLength(255),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('slug')
                                ->label('成果網址')->prefix(url('/results') . '/')
                                ->required()->maxLength(150)->unique(ignoreRecord: true)
                                ->helperText('例如 fushan-forest-composition；不需輸入 results/。'),
                            Forms\Components\Toggle::make('is_public')
                                ->label('發布到前台')->default(true),
                        ]),
                    ]),

                Tabs\Tab::make('關聯設定')
                    ->icon('heroicon-o-link')
                    ->schema([
                        ...ContentRelationForm::fields(),
                    ])->columns(2),

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
                Tables\Columns\IconColumn::make('is_public')->label('公開')->boolean(),
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
