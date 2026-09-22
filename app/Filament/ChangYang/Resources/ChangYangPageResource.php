<?php

namespace App\Filament\ChangYang\Resources;

use App\Filament\ChangYang\Resources\ChangYangPageResource\Pages;
use App\Forms\Components\ImageFrameEditor;
use App\Forms\Components\PersonContentEditor;
use App\Models\ChangYang\Page;
use App\Models\ChangYang\Person;
use App\Models\ChangYang\PersonCategory;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChangYangPageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationGroup = '頁面編輯';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('頁面設定')->tabs([
                Tabs\Tab::make('頁面與首圖')->icon('heroicon-o-photo')->schema([
                    Forms\Components\TextInput::make('title')->label('頁面標題')->required()->maxLength(255),
                    Forms\Components\TextInput::make('navigation_label')->label('導覽名稱')->maxLength(100),
                    Forms\Components\Textarea::make('meta_description')->label('搜尋說明')->rows(2)->maxLength(500),
                    Forms\Components\FileUpload::make('hero_image_path')
                        ->label('首圖')->disk('public')->directory('changyang/heroes')->visibility('public')
                        // Hero images are often 0.7–1 MB. Avoid downloading one on
                        // every admin-page visit; the public-page action still opens it.
                        ->image()->imageEditor()->previewable(false)->openable()->downloadable()->live(),
                    Forms\Components\TextInput::make('hero_image_alt')->label('首圖替代文字'),
                    Forms\Components\TextInput::make('hero_title')->label('首圖主標題')->live(onBlur: true),
                    Forms\Components\Textarea::make('hero_subtitle')->label('首圖副標題')->rows(2)->live(onBlur: true),
                    ImageFrameEditor::make('hero_settings')
                        ->label('首圖取樣與預覽')
                        ->imagePath(fn (Forms\Get $get): mixed => $get('hero_image_path'))
                        ->previewData(fn (Forms\Get $get): array => [
                            'mode' => 'hero',
                            'heading' => $get('hero_title'),
                            'subtitle' => $get('hero_subtitle'),
                        ])
                        ->columnSpanFull(),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Toggle::make('show_in_navigation')->label('顯示在導覽列'),
                        Forms\Components\Toggle::make('is_active')->label('公開頁面'),
                    ]),
                ]),
                Tabs\Tab::make('頁面內容')->icon('heroicon-o-document-text')->schema([
                    static::blocksField(),
                ])->visible(fn (?Page $record): bool => ! in_array($record?->slug, ['people', 'news', 'gallery', 'publications'], true)),
                Tabs\Tab::make('消息列表')->id('news')->icon('heroicon-o-newspaper')->schema([
                    Forms\Components\View::make('filament.changyang.forms.news-list-tab')
                        ->viewData(fn (): array => ['items' => \App\Models\ChangYang\NewsItem::query()
                            ->select(['id', 'category_year', 'category_month', 'content_html', 'sort_order', 'is_active'])
                            ->latestFirst()
                            ->get()]),
                ])->visible(fn (?Page $record): bool => $record?->slug === 'news'),
                Tabs\Tab::make('相簿管理')->id('gallery')->icon('heroicon-o-photo')->schema([
                    Forms\Components\View::make('filament.changyang.forms.gallery-list-tab')
                        ->viewData(fn (): array => ['albums' => \App\Models\ChangYang\Gallery::query()
                            ->withCount('items')
                            ->with(['previewItem' => fn ($query) => $query->select([
                                'changyang_gallery_items.id',
                                'changyang_gallery_items.gallery_id',
                                'changyang_gallery_items.image_path',
                            ])])
                            ->orderBy('sort_order')->orderBy('id')->paginate(24, pageName: 'gallery_page')]),
                ])->visible(fn (?Page $record): bool => $record?->slug === 'gallery'),
                Tabs\Tab::make('學術產出列表')->id('publications')->icon('heroicon-o-book-open')->schema([
                    Forms\Components\View::make('filament.changyang.forms.publication-list-tab')
                        ->viewData(fn (): array => ['publications' => \App\Models\Web\Publication::query()
                            ->where('is_changyang', true)
                            ->orderByDesc('year')->orderBy('title')
                            ->paginate(25, pageName: 'publication_page')]),
                ])->visible(fn (?Page $record): bool => $record?->slug === 'publications'),
                Tabs\Tab::make('身分類型')->icon('heroicon-o-tag')->schema([
                    static::personCategoriesField(),
                ])->visible(fn (?Page $record): bool => $record?->slug === 'people'),
                Tabs\Tab::make('人員')->id('people')->icon('heroicon-o-users')->schema([
                    Forms\Components\View::make('filament.changyang.forms.people-list-tab')
                        ->viewData(fn (): array => [
                            'categories' => PersonCategory::query()->orderBy('sort_order')->orderBy('id')
                                ->with(['roles' => fn ($query) => $query->where('is_current', true)
                                    ->select(['id', 'person_id', 'category_id', 'sort_order', 'is_current'])
                                    ->with(['person:id,name,image_path,image_alt,is_active,sort_order'])
                                    ->orderBy('sort_order')->orderBy('id')])->get(),
                            'unassigned' => Person::query()
                                ->select(['id', 'name', 'image_path', 'image_alt', 'is_active', 'sort_order'])
                                ->whereDoesntHave('roles', fn ($query) => $query->where('is_current', true))
                                ->orderBy('sort_order')->get(),
                        ]),
                ])->visible(fn (?Page $record): bool => $record?->slug === 'people'),
            ])->persistTabInQueryString()->columnSpanFull(),
        ]);
    }

    public static function peopleInCategoryOrder(): \Illuminate\Support\Collection
    {
        $categoryOrder = PersonCategory::query()
            ->orderBy('sort_order')->orderBy('id')->pluck('id')->flip();

        return Person::query()
            ->with(['roles' => fn ($query) => $query->where('is_current', true)->with('category')])
            ->orderBy('sort_order')->orderBy('id')->get()
            ->sortBy(fn (Person $person): int => $person->roles
                ->map(fn ($role): int => $categoryOrder->get($role->category_id, PHP_INT_MAX))
                ->min() ?? PHP_INT_MAX)
            ->values();
    }

    protected static function blocksField(): Repeater
    {
        return Repeater::make('blocks')
            ->label('內容區塊')
            ->relationship()
            ->orderColumn('sort_order')
            ->addActionLabel('新增內容區塊')
            ->reorderable()
            ->collapsible()
            ->collapsed()
            ->itemLabel(fn (array $state): string => $state['heading'] ?: '未命名區塊')
            ->schema([
                Forms\Components\Select::make('layout')->label('版面')->options([
                    'text_only' => '純文字', 'image_left' => '圖片在左', 'image_right' => '圖片在右',
                ])->default('text_only')->live()->required(),
                Forms\Components\Textarea::make('heading')->label('區塊標題')->rows(2)->maxLength(255)->live(onBlur: true),
                PersonContentEditor::make('content_html')->label('主要內容')->live(debounce: 750),
                Forms\Components\Toggle::make('is_active')->label('顯示於前台')->default(true),
                Repeater::make('images')
                    ->label('圖片')
                    ->relationship()
                    ->orderColumn('sort_order')
                    ->addable(fn ($state): bool => count($state ?? []) < 1)
                    ->deletable()
                    ->deleteAction(fn ($action) => $action->requiresConfirmation()->modalDescription('移除此區塊的圖片關聯，原始圖片檔案會保留。'))
                    ->reorderable(false)
                    ->maxItems(1)
                    ->afterStateHydrated(function (Repeater $component, ?array $state): void {
                        if (empty($state)) {
                            $component->state([(string) \Illuminate\Support\Str::uuid() => []]);
                        }
                    })
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): ?array => filled($data['image_path'] ?? null) ? $data : null)
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('圖片檔案')->disk('public')->directory('changyang/content')->visibility('public')
                            ->image()->previewable(false)->live()->required(fn ($record): bool => $record !== null)
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set): void {
                                if (blank($get('alt_text'))) {
                                    $set('alt_text', $get('../../heading'));
                                }
                            }),
                        Forms\Components\TextInput::make('alt_text')
                            ->default(fn (Forms\Get $get): mixed => $get('../../heading'))
                            ->hidden()
                            ->dehydratedWhenHidden(),
                        Forms\Components\Hidden::make('caption')
                            ->default(fn (Forms\Get $get) => $get('../../heading'))
                            ->afterStateHydrated(function (Forms\Components\Hidden $component, Forms\Get $get, $state): void {
                                if (blank($state)) {
                                    $component->state($get('../../heading'));
                                }
                            })
                            ->dehydrateStateUsing(fn ($state, Forms\Get $get) => filled($state) ? $state : $get('../../heading')),
                        Forms\Components\Hidden::make('show_photographer')->default(false)->dehydrated(false),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('showPhotographer')
                                ->label('標示拍攝者')
                                ->icon('heroicon-o-camera')
                                ->action(fn (Forms\Set $set) => $set('show_photographer', true)),
                        ])->visible(fn (Forms\Get $get): bool => ! $get('show_photographer') && blank($get('photographer'))),
                        Forms\Components\TextInput::make('photographer')
                            ->label('拍攝者')->maxLength(255)->live(onBlur: true)
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('show_photographer') || filled($get('photographer'))),
                        ImageFrameEditor::make('display_settings')
                            ->label('圖片取樣與顯示')
                            ->imagePath(fn (Forms\Get $get): mixed => $get('image_path'))
                            ->visible(fn (Forms\Get $get): bool => filled($get('image_path')))
                            ->previewData(fn (Forms\Get $get): array => [
                                'heading' => $get('../../heading'),
                                'content' => $get('../../content_html'),
                                'layout' => $get('../../layout'),
                                'photographer' => $get('photographer'),
                                'mode' => 'block',
                                'caption' => $get('caption'),
                                'alt_text' => $get('alt_text'),
                            ]),
                    ]),
            ]);
    }

    protected static function personCategoriesField(): Repeater
    {
        return Repeater::make('person_categories')
            ->label(false)
            ->helperText('直接修改顯示標題或調整排序；按下本頁的「儲存變更」後套用。')
            ->dehydrated(false)
            ->addActionLabel('新增身分類型')
            ->deletable(false)
            ->reorderable()
            ->afterStateHydrated(function (Repeater $component): void {
                $component->state(
                    PersonCategory::query()
                        ->withCount('roles')
                        ->orderBy('sort_order')
                        ->get()
                        ->mapWithKeys(fn (PersonCategory $category): array => [
                            'category-'.$category->id => [
                                'id' => $category->id,
                                'title' => $category->title,
                                'roles_count' => $category->roles_count,
                                'is_active' => $category->is_active,
                            ],
                        ])
                        ->all()
                );
            })
            ->schema([
                Forms\Components\Hidden::make('id'),
                Forms\Components\Grid::make(12)->schema([
                    Forms\Components\TextInput::make('title')->label('顯示標題')->required()->columnSpan(7),
                    Forms\Components\Placeholder::make('roles_count')->label('人物角色數')->content(fn (Forms\Get $get): string => (string) $get('roles_count'))->columnSpan(2),
                    Forms\Components\Toggle::make('is_active')->label('顯示')->columnSpan(3),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('navigation_order')
            ->reorderable('navigation_order')
            ->columns([
                Tables\Columns\TextColumn::make('navigation_label')->label('頁面')->searchable(),
                Tables\Columns\TextColumn::make('title')->label('標題')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('公開')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('編輯')]);
    }

    public static function getNavigationItems(): array
    {
        return Page::query()->orderBy('navigation_order')->orderBy('id')->get()
            ->map(fn (Page $page): NavigationItem => NavigationItem::make($page->navigation_label ?: $page->title)
                ->group('頁面編輯')
                ->icon('heroicon-o-document-text')
                ->sort($page->navigation_order)
                ->url(static::getUrl('edit', ['record' => $page])))
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChangYangPages::route('/'),
            'edit' => Pages\EditChangYangPage::route('/{record}/edit'),
        ];
    }
}
