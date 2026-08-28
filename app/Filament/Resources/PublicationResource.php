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
use Filament\Tables\Enums\FiltersLayout;
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
                            static::typeField()->columnSpan(4),
                            static::languageField()->columnSpan(4),
                            Forms\Components\TextInput::make('year')->label('Year')->numeric()->columnSpan(4),
                            Forms\Components\Textarea::make('authors')
                                ->label('Authors')
                                ->required()
                                ->rows(4)
                                ->columnSpan(fn (Forms\Get $get): int => static::showsChineseFields($get) ? 6 : 12),
                            Forms\Components\Textarea::make('authors_zh_tw')
                                ->label('中文作者')
                                ->rows(4)
                                ->columnSpan(6)
                                ->visible(fn (Forms\Get $get): bool => static::showsChineseFields($get)),
                            Forms\Components\Textarea::make('title')
                                ->label('Title')
                                ->required()
                                ->rows(3)
                                ->maxLength(500)
                                ->columnSpan(fn (Forms\Get $get): int => static::showsChineseFields($get) ? 6 : 12),
                            Forms\Components\Textarea::make('title_zh_tw')
                                ->label('中文標題')
                                ->rows(3)
                                ->maxLength(500)
                                ->columnSpan(6)
                                ->visible(fn (Forms\Get $get): bool => static::showsChineseFields($get)),
                            static::thesisTypeField()->columnSpan(4),
                            static::pairedAutocompleteField('institution', 'Institution (English)', 'institution_zh_tw')
                                ->columnSpan(4)
                                ->visible(fn (Forms\Get $get): bool => $get('type') === 'thesis'),
                            static::pairedAutocompleteField('institution_zh_tw', '學校名稱（中文）', 'institution')
                                ->columnSpan(4)
                                ->visible(fn (Forms\Get $get): bool => $get('type') === 'thesis'),
                            static::pairedAutocompleteField('journal', 'Journal', 'journal_zh_tw')
                                ->columnSpan(fn (Forms\Get $get): int => $get('language') === 'zh' ? 3 : 6)
                                ->hidden(fn (Forms\Get $get): bool => $get('type') === 'thesis'),
                            static::pairedAutocompleteField('journal_zh_tw', '中文期刊名稱', 'journal')
                                ->columnSpan(3)
                                ->visible(fn (Forms\Get $get): bool => $get('type') !== 'thesis' && $get('language') === 'zh'),
                            Forms\Components\TextInput::make('volume')
                                ->label('Volume')
                                ->maxLength(50)
                                ->columnSpan(1)
                                ->hidden(fn (Forms\Get $get): bool => $get('type') === 'thesis'),
                            Forms\Components\TextInput::make('issue')
                                ->label('Issue')
                                ->maxLength(50)
                                ->columnSpan(1)
                                ->hidden(fn (Forms\Get $get): bool => $get('type') === 'thesis'),
                            Forms\Components\TextInput::make('pages')
                                ->label('Pages')->placeholder('例如：100–123')
                                ->helperText('請輸入頁碼或頁碼範圍。')
                                ->maxLength(100)->columnSpan(2)
                                ->hidden(fn (Forms\Get $get): bool => $get('type') === 'thesis'),
                            Forms\Components\TextInput::make('doi')
                                ->label('DOI')
                                ->maxLength(255)
                                ->columnSpan(2)
                                ->hidden(fn (Forms\Get $get): bool => $get('type') === 'thesis'),
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
            Tables\Columns\IconColumn::make('is_active')->label('公開')->boolean(),
        ])
            ->defaultSort('year', 'desc')
            ->defaultKeySort()
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('文獻類型')
                    ->options(fn (): array => static::publicationTypeOptions())
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('site')
                    ->label('樣區')
                    ->relationship('sites', 'name_zh_tw')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('subject')
                    ->label('研究主題')
                    ->relationship('subjects', 'name_zh_tw')
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns([
                'default' => 1,
                'md' => 3,
            ])
            ->toggleColumnsTriggerAction(
                fn (Tables\Actions\Action $action): Tables\Actions\Action => $action
                    ->label('顯示／隱藏欄位')
                    ->button()
            )
            ->actions(
            [
                Tables\Actions\EditAction::make()->label('編輯'),
            ],
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
            ->options(fn (): array => static::publicationTypeOptions())
            ->default('paper')
            ->required()
            ->live()
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

    protected static function publicationTypeOptions(): array
    {
        $labels = Publication::typeLabels('zh-TW');
        $options = [
            'journalArticle' => $labels['journalArticle'],
            'thesis' => $labels['thesis'],
            'book' => $labels['book'],
            'dataset' => $labels['dataset'],
            'paper' => $labels['paper'],
            'poster' => $labels['poster'],
            'oral' => $labels['oral'],
        ];

        Publication::query()
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->each(function (string $type) use (&$options, $labels): void {
                $label = $labels[$type] ?? $type;

                if (! array_key_exists($type, $options) && ! in_array($label, $options, true)) {
                    $options[$type] = $label;
                }
            });

        return $options;
    }

    protected static function languageField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('language')
            ->label('Language')
            ->options([
                'en' => 'English',
                'zh' => 'Chinese',
            ])
            ->default('en')
            ->required()
            ->live()
            ->native(false);
    }

    protected static function thesisTypeField(): Forms\Components\Select
    {
        return Forms\Components\Select::make('thesis_type')
            ->label('Thesis type')
            ->options([
                'master' => "Master's thesis",
                'doctoral' => 'Doctoral dissertation',
            ])
            ->placeholder('請選擇')
            ->native(false)
            ->visible(fn (Forms\Get $get): bool => $get('type') === 'thesis');
    }

    protected static function pairedAutocompleteField(
        string $field,
        string $label,
        string $pairedField,
    ): Forms\Components\TextInput {
        return Forms\Components\TextInput::make($field)
            ->label($label)
            ->maxLength(255)
            ->datalist(fn (): array => Publication::query()
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->distinct()
                ->orderBy($field)
                ->pluck($field)
                ->all())
            ->live(onBlur: true)
            ->afterStateUpdated(function (?string $state, Forms\Set $set) use ($field, $pairedField): void {
                if (blank($state)) {
                    return;
                }

                $pairedValue = Publication::query()
                    ->where($field, $state)
                    ->whereNotNull($pairedField)
                    ->where($pairedField, '!=', '')
                    ->value($pairedField);

                if (filled($pairedValue)) {
                    $set($pairedField, $pairedValue);
                }
            });
    }

    protected static function showsChineseFields(Forms\Get $get): bool
    {
        return $get('type') === 'thesis' || $get('language') === 'zh';
    }
}
