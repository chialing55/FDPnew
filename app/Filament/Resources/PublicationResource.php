<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PublicationResource\Pages;
use App\Filament\Forms\ContentRelationForm;
use App\Models\Web\Publication;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PublicationResource extends Resource
{
    protected static ?string $model = Publication::class;
    protected static ?string $navigationGroup = '研究成果';
    protected static ?string $navigationLabel = '學術產出';
    protected static ?string $modelLabel = '學術產出';
    protected static ?string $pluralModelLabel = '學術產出';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('學術產出設定')->tabs([
                Tabs\Tab::make('基本資料')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(12)->schema([
                            Forms\Components\TextInput::make('authors')->label('Authors')->maxLength(1000)->columnSpan(4),
                            Forms\Components\TextInput::make('year')->label('Year')->numeric()->columnSpan(2),
                            Forms\Components\TextInput::make('title')->label('Title')->maxLength(500)->columnSpan(6),
                            Forms\Components\TextInput::make('journal')->label('Journal')->maxLength(255)->columnSpan(4),
                            Forms\Components\TextInput::make('volume')->label('Volume')->maxLength(50)->columnSpan(2),
                            Forms\Components\TextInput::make('issue')->label('Issue')->maxLength(50)->columnSpan(2),
                            Forms\Components\TextInput::make('pages')
                                ->label('Pages')->placeholder('例如：100–123')
                                ->helperText('請輸入頁碼或頁碼範圍。')
                                ->maxLength(100)->columnSpan(2),
                            Forms\Components\TextInput::make('doi')->label('DOI')->maxLength(255)->columnSpan(2),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('url')
                                ->label('出版社或資料庫頁面連結')->url()
                                ->helperText('DOI 以外的網頁連結，例如出版社文章頁面。'),
                            Forms\Components\FileUpload::make('pdf_path')
                                ->label('論文 PDF')->disk('public')->directory('publications')
                                ->visibility('public')->acceptedFileTypes(['application/pdf'])
                                ->openable()->downloadable()
                                ->helperText('有可公開的 PDF 檔案時才上傳。'),
                        ]),
                        Forms\Components\Toggle::make('is_open_access')->label('Open Access'),
                    ]),
                Tabs\Tab::make('關聯設定')
                    ->icon('heroicon-o-link')
                    ->schema([
                        ...ContentRelationForm::fields(),
                    ])->columns(2),
            ])->persistTabInQueryString()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('year')->label('年份')->sortable(),
            Tables\Columns\TextColumn::make('authors')->label('作者')->searchable()->wrap()->toggleable(),
            Tables\Columns\TextColumn::make('title')->label('標題')->searchable()->wrap(),
            Tables\Columns\TextColumn::make('journal')->label('期刊')->wrap(),
            Tables\Columns\TextColumn::make('sites.name_zh_tw')
                ->label('樣區')->badge()->separator(', '),
            Tables\Columns\TextColumn::make('subjects.name_zh_tw')
                ->label('研究主題')->badge()->separator(', '),
            Tables\Columns\TextColumn::make('doi')->label('DOI')->searchable(),
            Tables\Columns\IconColumn::make('is_open_access')->label('Open Access')->boolean(),
        ])->defaultSort('year', 'desc')->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPublications::route('/'), 'create' => Pages\CreatePublication::route('/create'), 'edit' => Pages\EditPublication::route('/{record}/edit')];
    }
}
