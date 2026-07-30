<?php

namespace App\Filament\Resources;

use App\Filament\Forms\ContentRelationForm;
use App\Filament\Resources\PublicationResource\Pages;
use App\Models\Web\Publication;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
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
        if ($form->getOperation() === 'create') {
            return $form->schema([
                Forms\Components\Section::make('基本資料')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Textarea::make('authors')
                            ->label('Authors')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('year')
                            ->label('Year')
                            ->numeric(),
                        static::typeField(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('顯示於前台')
                            ->default(true),
                    ]),
                ]),
                Forms\Components\Section::make('中文資料（選填）')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('authors_zh_tw')
                            ->label('中文作者')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('title_zh_tw')
                            ->label('中文標題')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('journal_zh_tw')
                            ->label('中文期刊名稱')
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
        }

        return $form->schema([
            Tabs::make('學術產出設定')->tabs([
                Tabs\Tab::make('基本資料')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(12)->schema([
                            Forms\Components\Textarea::make('authors')
                                ->label('Authors')
                                ->rows(4)
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('year')->label('Year')->numeric()->columnSpan(2),
                            static::typeField()->columnSpan(2),
                            Forms\Components\TextInput::make('title')->label('Title')->maxLength(500)->columnSpan(4),
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
                        Forms\Components\Toggle::make('is_active')->label('顯示於前台')->default(true),
                    ]),
                Tabs\Tab::make('中文資料（選填）')
                    ->icon('heroicon-o-language')
                    ->schema([
                        Forms\Components\Textarea::make('authors_zh_tw')
                            ->label('中文作者')
                            ->rows(4)
                            ->helperText('沒有中文資料時請留空，中文前台會自動顯示原始書目資料。'),
                        Forms\Components\TextInput::make('title_zh_tw')
                            ->label('中文標題')
                            ->maxLength(500),
                        Forms\Components\TextInput::make('journal_zh_tw')
                            ->label('中文期刊名稱')
                            ->maxLength(255),
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
            Tables\Columns\TextColumn::make('type')
                ->label('類型')
                ->formatStateUsing(fn (?string $state): string => Publication::typeLabels('zh-TW')[$state] ?? $state ?? '')
                ->badge()
                ->sortable(),
            Tables\Columns\TextColumn::make('abbreviated_authors')
                ->label('作者')
                ->searchable(query: fn ($query, string $search) => $query->where(
                    fn ($query) => $query
                        ->where('authors', 'like', "%{$search}%")
                        ->orWhere('authors_zh_tw', 'like', "%{$search}%")
                ))
                ->wrap()
                ->width('18rem'),
            Tables\Columns\TextColumn::make('title')->label('標題')->searchable()->limit(70)->wrap()->width('22rem'),
            Tables\Columns\TextColumn::make('title_zh_tw')
                ->label('中文標題')
                ->searchable()
                ->limit(70)
                ->wrap()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('journal')->label('期刊')->limit(35)->wrap()->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('sites.name_zh_tw')
                ->label('樣區')->badge()->separator(', '),
            Tables\Columns\TextColumn::make('subjects.name_zh_tw')
                ->label('研究主題')->badge()->separator(', '),
            Tables\Columns\TextColumn::make('doi')->label('DOI')->searchable()->limit(30)->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\IconColumn::make('is_open_access')->label('Open Access')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->label('公開')->boolean(),
        ])->defaultSort('year', 'desc')->actions(
            [Tables\Actions\EditAction::make()],
            ActionsPosition::BeforeColumns,
        );
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListPublications::route('/'), 'create' => Pages\CreatePublication::route('/create'), 'edit' => Pages\EditPublication::route('/{record}/edit')];
    }

    protected static function typeField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('type')
            ->label('Type')
            ->options(function (): array {
                $labels = Publication::typeLabels('zh-TW');

                Publication::query()
                    ->whereNotNull('type')
                    ->where('type', '!=', '')
                    ->distinct()
                    ->orderBy('type')
                    ->pluck('type')
                    ->each(function (string $type) use (&$labels): void {
                        $labels[$type] ??= $type;
                    });

                return $labels;
            })
            ->default('paper')
            ->required()
            ->searchable()
            ->preload()
            ->native(false)
            ->createOptionForm([
                Forms\Components\TextInput::make('type')
                    ->label('新增類型')
                    ->required()
                    ->maxLength(50),
            ])
            ->createOptionUsing(fn (array $data): string => strtolower(trim($data['type'])))
            ->helperText('可選擇既有類型，或新增其他類型。');
    }
}
