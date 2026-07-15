<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Filament\Forms\ContentBlockForm;
use App\Filament\Forms\ImmediatePublicImage;
use App\Forms\Components\HtmlContentEditor;
use App\Models\Web\Page;
use App\Models\Web\Site;
use App\Models\Web\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Filament\Forms\Components\Tabs;
use Filament\Navigation\NavigationItem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PageResource extends Resource
{
    private const FIXED_LIST_PAGE_SLUGS = ['results', 'projects', 'about/news', 'about/team'];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private const SLUG_SETTINGS = [
        'about' => ['prefix' => 'about/', 'example' => 'contact'],
        'sites' => ['prefix' => 'sites/', 'example' => 'fushan'],
        'subjects' => ['prefix' => 'subjects/', 'example' => 'seedling'],
    ];

    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = '網站頁面';
    protected static ?string $pluralModelLabel = '頁面';
    protected static ?string $modelLabel = '頁面';
    protected static ?string $navigationGroup = '關於我們';
    protected static ?int $navigationSort = 1;

    // 共用頁面編輯器是 CMS 的主要入口，允許所有可進入 CMS 的帳號使用。
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Filament::auth()->user();

        return $user?->canAccessFilament() ?? false;
    }

    public static function getNavigationItems(): array
    {
        if (! static::canViewAny()) {
            return [];
        }

        $items = [];

        try {
            Page::query()->where('nav_group', 'about')->where('slug', '!=', 'about/news')->orderBy('nav_order')->orderBy('id')
                ->get()->each(function (Page $page) use (&$items): void {
                    $items[] = NavigationItem::make($page->title_zh_tw)
                        ->group('關於我們')->icon('heroicon-o-document-text')->sort($page->nav_order ?? $page->id)
                        ->url(static::getUrl('edit', ['record' => $page], isAbsolute: false))
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.pages.edit')
                            && (int) request()->route('record') === (int) $page->getKey());
                });
        } catch (\Throwable) {
            // 資料庫尚未就緒時仍保留新增入口。
        }

        $items[] = NavigationItem::make('新增關於頁面')
            ->group('關於我們')->icon('heroicon-o-plus-circle')->sort(998)
            ->url(static::getUrl('create'))
            ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.pages.create'));

        return $items;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('頁面設定')
                    ->tabs([
                        Tabs\Tab::make('基本資料')
                            ->icon('heroicon-o-document-text')
                            ->visible(fn (?Page $record): bool => $record?->slug !== 'index')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('title_zh_tw')
                                        ->label('中文標題')
                                        ->required()
                                        ->maxLength(255)
                                        ->visible(fn (?Page $record): bool => $record?->nav_group !== 'subjects'),
                                    Forms\Components\TextInput::make('subject_short_name_zh_tw')
                                        ->label('中文標題')
                                        ->required()
                                        ->maxLength(100)
                                        ->visible(fn (?Page $record): bool => $record?->nav_group === 'subjects'),
                                    Forms\Components\TextInput::make('title_en')
                                        ->label('英文標題')
                                        ->required()
                                        ->maxLength(255)
                                        ->visible(fn (?Page $record): bool => $record?->nav_group !== 'subjects'),
                                    Forms\Components\TextInput::make('subject_short_name_en')
                                        ->label('英文標題')
                                        ->required()
                                        ->maxLength(100)
                                        ->visible(fn (?Page $record): bool => $record?->nav_group === 'subjects'),
                                    static::slugField(),
                                    Forms\Components\Toggle::make('page_visibility')
                                        ->label('顯示於前台')
                                        ->visible(fn (?Page $record): bool => in_array($record?->nav_group, ['sites', 'subjects'], true))
                                        ->afterStateHydrated(function (Forms\Components\Toggle $component, ?Page $record): void {
                                            $component->state($record?->site?->is_active ?? $record?->subject?->is_active ?? true);
                                        }),
                                ]),
                                Forms\Components\Section::make('完整標題')
                                    ->visible(fn (?Page $record): bool => $record?->nav_group === 'subjects')
                                    ->schema([
                                        Forms\Components\Grid::make(2)->schema([
                                            Forms\Components\TextInput::make('subject_name_zh_tw')
                                                ->label('完整標題（中）')
                                                ->required()
                                                ->maxLength(255),
                                            Forms\Components\TextInput::make('subject_name_en')
                                                ->label('完整標題（英）')
                                                ->required()
                                                ->maxLength(255),
                                        ]),
                                    ]),
                                Forms\Components\Section::make('動態樣區設定')
                                    ->relationship('site')
                                    ->visible(fn (?Page $record): bool => $record?->nav_group === 'sites')
                                    ->schema([
                                        Forms\Components\Grid::make(3)->schema([
                                            Forms\Components\TextInput::make('latitude')->label('中心點緯度')->numeric(),
                                            Forms\Components\TextInput::make('longitude')->label('中心點經度')->numeric(),
                                            Forms\Components\TextInput::make('elevation_m')->label('海拔（公尺）')->numeric(),
                                            Forms\Components\TextInput::make('plot_area_ha')
                                                ->label('樣區面積')
                                                ->numeric()
                                                ->minValue(0)
                                                ->suffix('公頃'),
                                            Forms\Components\TextInput::make('established_year')
                                                ->label('設立時間')
                                                ->integer()
                                                ->minValue(1800)
                                                ->maxValue((int) date('Y'))
                                                ->suffix('年'),
                                        ]),
                                    ]),
                            ]),
                        static::siteIntroductionTab(),
                        Tabs\Tab::make('頁面 Hero')
                            ->icon('heroicon-o-photo')
                            ->visible(fn (?Page $record): bool => $record?->slug !== 'index')
                            ->schema([
                                Forms\Components\Section::make('選擇頁面 Hero')
                                    ->description('從 Hero 圖庫選擇一張圖片作為此頁面的 Hero，若無指定則隨機顯示。')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('hero_image')
                                            ->label('現有照片')
                                            ->options(fn (?Page $record): array => static::heroOptions($record?->hero_image))
                                            ->allowHtml()->columns(3)
                                            ->afterStateHydrated(function (Forms\Components\CheckboxList $component, mixed $state): void {
                                                $component->state(filled($state) ? [$state] : []);
                                            })
                                            ->afterStateUpdated(function (Forms\Components\CheckboxList $component, mixed $state): void {
                                                if (is_array($state) && count($state) > 1) {
                                                    $component->state([array_values($state)[count($state) - 1]]);
                                                }
                                            })
                                            ->dehydrateStateUsing(fn (mixed $state): ?string => is_array($state) ? (array_values($state)[0] ?? null) : $state)
                                            ->helperText('點選一張照片後，使用頁面底部的 Save changes 儲存。')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('頁面內容')
                            ->icon('heroicon-o-rectangle-stack')
                            ->visible(fn (?Page $record): bool => ! in_array($record?->slug, self::FIXED_LIST_PAGE_SLUGS, true))
                            ->schema([
                                Forms\Components\Placeholder::make('subject_content_notice')
                                    ->label('研究主題內容區塊說明')
                                    ->content('研究主題頁面需建立以下兩個內容區塊：「簡介 / Introduction」與「研究方法 / Methods」。')
                                    ->visible(fn (?Page $record): bool => $record?->nav_group === 'subjects'),
                                ContentBlockForm::make('')->columnSpanFull(),
                            ]),
                        static::siteTeamsTab(),
                    ])->persistTabInQueryString()->columnSpanFull(),
            ]);
    }

    protected static function slugField(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('slug')
            ->label('頁面網址')
            ->prefix(fn (?Page $record, Forms\Get $get): string => url('/') . '/' . static::slugPrefix(static::navGroup($record, $get)))
            ->required()
            ->formatStateUsing(fn (?string $state, ?Page $record): string => static::slugTail($state, $record?->nav_group))
            ->dehydrateStateUsing(fn (?string $state, ?Page $record, Forms\Get $get): string => static::fullSlug($state, static::navGroup($record, $get)))
            ->rule(function (?Page $record, Forms\Get $get): \Closure {
                return function (string $attribute, mixed $value, \Closure $fail) use ($record, $get): void {
                    $slug = static::fullSlug((string) $value, static::navGroup($record, $get));
                    $exists = Page::query()->where('slug', $slug)
                        ->when($record, fn ($query) => $query->where('id', '!=', $record->getKey()))
                        ->exists();

                    if ($exists) {
                        $fail('此頁面網址已經被使用。');
                    }
                };
            })
            ->helperText(fn (?Page $record, Forms\Get $get): string => static::slugHelperText(static::navGroup($record, $get)));
    }

    protected static function navGroup(?Page $record, Forms\Get $get): ?string
    {
        return $record?->nav_group ?? $get('nav_group');
    }

    protected static function slugPrefix(?string $navGroup): string
    {
        return self::SLUG_SETTINGS[$navGroup]['prefix'] ?? '';
    }

    protected static function slugTail(?string $slug, ?string $navGroup): string
    {
        $slug = ltrim((string) $slug, '/');
        $prefix = static::slugPrefix($navGroup);

        return $prefix !== '' && str_starts_with($slug, $prefix)
            ? substr($slug, strlen($prefix))
            : $slug;
    }

    protected static function fullSlug(?string $slug, ?string $navGroup): string
    {
        return static::slugPrefix($navGroup) . static::slugTail($slug, $navGroup);
    }

    protected static function slugHelperText(?string $navGroup): string
    {
        $settings = self::SLUG_SETTINGS[$navGroup] ?? null;

        if (! $settings) {
            return '請填完整網址路徑。頁面公開後請勿隨意修改。';
        }

        $example = $settings['example'];

        return "只需填最後一段，例如 {$example}。頁面公開後請勿隨意修改。";
    }

    protected static function siteIntroductionTab(): Tabs\Tab
    {
        return Tabs\Tab::make('首頁樣區介紹')->icon('heroicon-o-map-pin')
            ->visible(fn (?Page $record): bool => $record?->nav_group === 'sites')
            ->schema([
                Forms\Components\Section::make()->relationship('site')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name_zh_tw')->label('樣區名稱（中）')->required(),
                        Forms\Components\TextInput::make('name_en')->label('樣區名稱（英）')->required(),
                    ]),
                    Forms\Components\Tabs::make('樣區簡介')->tabs([
                        Tabs\Tab::make('中文')->schema([HtmlContentEditor::make('description_zh_tw')->label('樣區簡介（中）')]),
                        Tabs\Tab::make('English')->schema([HtmlContentEditor::make('description_en')->label('Site introduction (English)')]),
                    ]),
                    ImmediatePublicImage::field('homepage_image', '首頁樣區卡片圖片', directory: 'plot-cards')
                        ->live()
                        ->helperText('選擇檔案後請按「儲存變更」；重新選擇會取代舊照片。'),
                    Forms\Components\TextInput::make('homepage_image_position')
                        ->label('圖片垂直顯示位置')
                        ->numeric()->minValue(1)->maxValue(100)->default(50)
                        ->live(debounce: 300)
                        ->suffix('%')
                        ->helperText('1 接近頂端、50 置中、100 接近底端；只調整前台顯示焦點，不會修改原圖。'),
                    Forms\Components\Placeholder::make('homepage_card_preview')
                        ->label('首頁卡片圖片預覽')
                        ->content(fn (Forms\Get $get, ?Site $record): HtmlString => static::homepageCardPreview($get, $record)),
                    Forms\Components\Actions::make([
                        Forms\Components\Actions\Action::make('deleteHomepageImage')
                            ->label('刪除照片')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->action(function (Forms\Set $set, ?Site $record): void {
                                if (! $record) {
                                    return;
                                }

                                ImmediatePublicImage::delete($record->homepage_image);
                                $record->update(['homepage_image' => null]);
                                $set('homepage_image', []);
                            }),
                    ])->visible(fn (?Site $record): bool => filled($record?->homepage_image)),
                ]),
            ]);
    }

    protected static function siteTeamsTab(): Tabs\Tab
    {
        return Tabs\Tab::make('研究團隊')
            ->icon('heroicon-o-users')
            ->visible(fn (?Page $record): bool => $record?->nav_group === 'sites')
            ->schema([
                Forms\Components\Section::make('研究團隊')
                    ->description('設定此動態樣區的負責團隊與合作單位；顯示順序數字越小越前面。')
                    ->schema([
                        Forms\Components\Placeholder::make('team_creation_notice')
                            ->label('新增團隊說明')
                            ->content(fn (): HtmlString => new HtmlString(
                                '若無符合的研究團隊，請至 <a class="font-semibold text-primary-600 hover:underline" href="'
                                . e(TeamResource::getUrl('index'))
                                . '">研究團隊頁面</a> 新增。'
                            )),
                        Forms\Components\Repeater::make('site_teams')
                            ->label('已連結團隊')
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, ?Page $record): void {
                                $component->state(
                                    $record?->site?->siteTeams()
                                        ->orderBy('sort_order')
                                        ->get(['id', 'team_id', 'role', 'sort_order'])
                                        ->map(fn ($siteTeam): array => $siteTeam->toArray())
                                        ->all() ?? []
                                );
                            })
                            ->schema([
                                Forms\Components\Hidden::make('id'),
                                Forms\Components\Select::make('team_id')
                                    ->label('團隊')
                                    ->options(fn (): array => Team::query()
                                        ->orderBy('institution_zh_tw')
                                        ->get()
                                        ->mapWithKeys(fn (Team $team): array => [$team->id => $team->display_name])
                                        ->all())
                                    ->searchable()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(2),
                                Forms\Components\Select::make('role')
                                    ->label('角色')
                                    ->options([
                                        'plot_manager' => '樣區負責人',
                                        'team_partner' => '合作單位',
                                    ])
                                    ->required()
                                    ->native(false),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('顯示順序')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->addActionLabel('新增研究團隊')
                            ->reorderableWithButtons()
                            ->orderColumn('sort_order')
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => Team::query()->find($state['team_id'] ?? null)?->display_name)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected static function heroOptions(?string $currentImage = null): array
    {
        $options = collect(Storage::disk('home_hero')->files())
            ->filter(fn (string $file): bool => static::isImage($file))
            ->mapWithKeys(fn (string $file): array => [
                'hero/' . $file => static::heroOption($file),
            ])->all();

        if ($currentImage && ! array_key_exists($currentImage, $options)) {
            $normalized = str_starts_with($currentImage, 'library:')
                ? 'hero/' . substr($currentImage, strlen('library:'))
                : $currentImage;

            if (array_key_exists($normalized, $options)) {
                $options[$currentImage] = $options[$normalized];
            }
        }

        return $options;
    }

    protected static function homepageCardPreview(Forms\Get $get, ?Site $record): HtmlString
    {
        $image = $record?->homepage_image;

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        $url = static::uploadedImageUrl($get('homepage_image'))
            ?? (is_string($image) && filled($image) ? $publicDisk->url($image) : null);

        if (! $url) {
            return new HtmlString('<div style="padding:24px;border:1px dashed #cbd5e1;border-radius:8px;color:#64748b;text-align:center">尚未選擇圖片</div>');
        }

        $position = max(1, min(100, (int) ($get('homepage_image_position') ?? 50)));

        return new HtmlString(
            '<div style="display:flex;width:100%;min-height:192px;overflow:hidden;border:1px solid #e5e7eb;border-radius:8px;background:#fff">'
            . '<div style="position:relative;width:60%;min-height:192px;overflow:hidden;border-radius:8px">'
            . '<img src="' . e($url) . '" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center ' . $position . '%">'
            . '</div>'
            . '<div style="display:flex;width:40%;align-items:center;justify-content:center;padding:16px;color:#94a3b8">文字區域（40%）</div>'
            . '</div>'
        );
    }

    protected static function uploadedImageUrl(mixed $image): ?string
    {
        if (is_array($image)) {
            $image = array_values($image)[0] ?? null;
        }

        /** @var FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');

        return match (true) {
            $image instanceof TemporaryUploadedFile => $image->temporaryUrl(),
            is_string($image) && filled($image) => $publicDisk->url($image),
            default => null,
        };
    }

    protected static function isImage(string $file): bool
    {
        return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true);
    }

    protected static function heroOption(string $file): HtmlString
    {
        $name = basename($file);
        /** @var FilesystemAdapter $heroDisk */
        $heroDisk = Storage::disk('home_hero');
        $url = $heroDisk->url($file);

        return new HtmlString(
            '<div style="padding:8px"><img src="' . e($url) . '" style="width:100%;height:110px;object-fit:cover;border-radius:8px" alt="">'
            . '<div style="margin-top:6px;word-break:break-all;font-size:12px">' . e($name) . '</div></div>'
        );
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label('頁面網址')
                    ->icon('heroicon-m-link')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title_zh_tw')
                    ->label('標題（中）')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title_en')
                    ->label('標題（英）')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nav_group')
                    ->label('導覽分類')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'about' => '關於我們', 'sites' => '動態樣區', 'subjects' => '研究主題',
                        'results' => '研究成果', 'others' => '其他頁面', default => '未分類',
                    }),

                Tables\Columns\TextColumn::make('nav_order')
                    ->label('導覽排序')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('description')
                    ->label('描述')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('hero_image')
                    ->label('hero image')
                    ->square()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('slug')
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('預覽')->icon('heroicon-o-eye')
                    ->url(fn (Page $record) => url('/' . ltrim($record->slug, '/')))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
