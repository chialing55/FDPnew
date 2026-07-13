<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Models\Web\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Web\Page;
use App\Filament\Resources\SiteResource\RelationManagers\SiteTeamsRelationManager;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\PageResource;
use App\Forms\Components\HtmlContentEditor;
use Illuminate\Support\Facades\Storage;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = '首頁';
    protected static ?string $navigationLabel = '樣區介紹';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = '樣區';
    protected static ?string $pluralModelLabel = '樣區';

    public static function getNavigationItems(): array
    {
        $items = [];

        try {
            Site::query()->with('page')->orderBy('id')->get()->each(function (Site $site) use (&$items): void {
                if (! $site->page) {
                    return;
                }
                $items[] = NavigationItem::make($site->short_name_zh_tw ?: $site->name_zh_tw)
                    ->group('動態樣區')->icon('heroicon-o-map-pin')->sort($site->id)
                    ->url(PageResource::getUrl('edit', ['record' => $site->page], isAbsolute: false))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.pages.edit')
                        && (int) request()->route('record') === (int) $site->page->getKey());
            });
        } catch (\Throwable) {
            // 資料庫尚未就緒時仍保留管理入口。
        }

        $items[] = NavigationItem::make('新增樣區')
            ->group('動態樣區')->icon('heroicon-o-plus-circle')->sort(999)
            ->url(static::getUrl('create'))
            ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.sites.create'));

        return $items;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本資訊')
                    ->schema([
                        Forms\Components\TextInput::make('page_slug')
                            ->label('頁面網址')
                            ->prefix(url('/') . '/')
                            ->placeholder('sites/example')
                            ->required(fn (?Site $record): bool => $record === null)
                            ->visible(fn (?Site $record): bool => $record === null)
                            ->unique(table: Page::class, column: 'slug')
                            ->helperText('建議使用 sites/英文樣區名稱，例如 sites/fushan。')
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)->schema([        
                            Forms\Components\TextInput::make('name_zh_tw')
                                ->label('樣區名稱（中）')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('name_en')
                                ->label('樣區名稱（英）')
                                ->required()
                                ->maxLength(255),
                        ]), 
                        Forms\Components\Tabs::make('樣區簡介')->tabs([
                            Forms\Components\Tabs\Tab::make('中文')->schema([
                                HtmlContentEditor::make('description_zh_tw')->label('樣區簡介（中）'),
                            ]),
                            Forms\Components\Tabs\Tab::make('English')->schema([
                                HtmlContentEditor::make('description_en')->label('Site introduction (English)'),
                            ]),
                        ])->columnSpanFull(),
                        Forms\Components\FileUpload::make('homepage_image')
                            ->label('首頁樣區卡片圖片')
                            ->disk('public')->directory('plot-cards')->visibility('public')
                            ->image()->imageEditor()->imagePreviewHeight('240')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('homepage_image_position')
                            ->label('圖片垂直顯示位置')
                            ->numeric()->minValue(1)->maxValue(100)->default(50)
                            ->suffix('%')
                            ->helperText('1 接近頂端、50 置中、100 接近底端；只調整前台顯示焦點，不會修改原圖。')
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(1)->schema([    
                            Forms\Components\Toggle::make('is_active')
                                ->label('是否顯示於前台')
                                ->default(true),
                        ]),    
                    ])->columns(2),

                Forms\Components\Section::make('地理資訊')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('中心點緯度')
                            ->numeric()
                            ->helperText('小數度，例如 24.7998'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('中心點經度')
                            ->numeric()
                            ->helperText('小數度，例如 121.5432'),

                        Forms\Components\TextInput::make('elevation_m')
                            ->label('海拔（公尺）')
                            ->numeric()
                            ->helperText('例如 750'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page.slug')
                    ->label('頁面 Slug')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('page.title_zh_tw')
                    ->label('頁面標題')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name_zh_tw')
                    ->label('樣區名稱（中）')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name_en')
                    ->label('樣區名稱（英）')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('前台顯示')
                    ->boolean(),

                Tables\Columns\TextColumn::make('elevation_m')
                    ->label('海拔（m）')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('是否顯示'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            SiteTeamsRelationManager::class,
        ];
    }

}
