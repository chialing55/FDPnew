<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentBlockResource\Pages;
use App\Models\Web\ContentBlock;
use App\Models\Web\Site;
use App\Models\Web\Subject;
use App\Models\Web\Page;
use App\Models\Web\ResearchOutput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use Wiebenieuwenhuis\FilamentCodeEditor\Components\CodeEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Set;
use Filament\Navigation\NavigationItem;

class ContentBlockResource extends Resource
{
    protected static ?string $model = ContentBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = '首頁';
    protected static ?string $navigationLabel = '網站介紹';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = '內容區塊';
    protected static ?string $pluralModelLabel = '內容區塊';

    public static function getNavigationItems(): array
    {
        return [];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('owner_type'),
                Forms\Components\Hidden::make('owner_id'),
                // ====== 基本設定 ======
                Forms\Components\Section::make('基本設定')
                    ->schema([
Forms\Components\Select::make('owner_selector')
    ->label('這段內容要放在哪裡？')
    ->helperText('可以輸入頁面標題或網址關鍵字搜尋。')
    ->required()
    ->searchable()
    ->preload()
    ->native(false) // 用 Filament 的美化 select
    ->options(function () {
        // 預先載入一批常用選項（例如全部或前幾筆）
        $options = [];

        Page::query()
            ->orderBy('slug')
            ->limit(50)
            ->get()
            ->each(function (Page $page) use (&$options) {
                $key = 'page:' . $page->id;
                $options[$key] = '頁面: ' . $page->slug . ' - ' . $page->title_zh_tw;
            });

        ResearchOutput::query()
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->each(function (ResearchOutput $output) use (&$options) {
                $key = 'result:' . $output->id;
                $options[$key] = '成果: ' . $output->slug . ' - ' . $output->title_zh_tw;
            });

        return $options;
    })
    ->getSearchResultsUsing(function (string $search): array {
        $results = [];

        // 🔍 搜 Page
        Page::query()
            ->where(function (Builder $query) use ($search) {
                $query->where('slug', 'like', "%{$search}%")
                    ->orWhere('title_zh_tw', 'like', "%{$search}%");
            })
            ->orderBy('slug')
            ->limit(20)
            ->get()
            ->each(function (Page $page) use (&$results) {
                $key = 'page:' . $page->id;
                $results[$key] = '頁面: ' . $page->slug . ' - ' . $page->title_zh_tw;
            });

        // 🔍 搜 ResearchOutput
        ResearchOutput::query()
            ->where(function (Builder $query) use ($search) {
                $query->where('title_zh_tw', 'like', "%{$search}%")
                    ->orWhere('title_en', 'like', "%{$search}%")
                    ;
            })
            ->orderBy('id')
            ->limit(20)
            ->get()
            ->each(function (ResearchOutput $output) use (&$results) {
                $key = 'result:' . $output->id;
                $results[$key] = '成果: ' . $output->slug . ' - ' . $output->title_zh_tw;
            });

        return $results;
    })
    ->getOptionLabelUsing(function (?string $value): ?string {
        if (! $value) {
            return null;
        }

        [$type, $id] = explode(':', $value);

        return match ($type) {
            'page' => optional(Page::find($id), fn ($page) =>
                '頁面: ' . $page->slug . ' - ' . $page->title_zh_tw),
            'result' => optional(ResearchOutput::find($id), fn ($output) =>
                '成果: ' . $output->slug . ' - ' . $output->title_zh_tw),
            default => null,
        };
    })
->afterStateUpdated(function ($state, Set $set) {
    if (! $state) {
        $set('owner_type', null);
        $set('owner_id', null);
        return;
    }

    [$type, $id] = explode(':', $state);

    // 這裡存「morphMap 的 key」，不是完整類別
    match ($type) {
        'page'   => $set('owner_type', 'pages'),             // 對應 morphMap 裡的 'pages'
        'result' => $set('owner_type', 'research_outputs'),  // 對應 morphMap 裡的 'research_outputs'
        default  => null,
    };

    $set('owner_id', (int) $id);
})

    ->afterStateHydrated(function ($state, Set $set, ?ContentBlock $record) {
        // 編輯時，依照 record 補上 owner_selector 的值
        if (! $record) {
            return;
        }

        $type = match ($record->owner_type) {
            'pages'            => 'page',
            'research_outputs' => 'result',
            default            => null,
        };

        if (! $type || ! $record->owner_id) {
            return;
        }

        $set('owner_selector', $type . ':' . $record->owner_id);
    })
    ->dehydrated(false), // 這個欄位本身不寫入資料庫，只用來填 owner_type / owner_id


                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('sort_order')
                                ->label('顯示順序')
                                ->numeric()
                                ->default(0)
                                ->helperText('數字越小越前面。'),
                            Forms\Components\Toggle::make('is_public')
                                ->label('顯示於前台')
                                ->default(true),
                        ]),
                    ])
                    ->columns(1),

                // ====== 標題與內容 ======
                Forms\Components\Section::make('標題與內容')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title_zh_tw')
                                ->label('區塊標題（中）')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('title_en')
                                ->label('區塊標題（英）')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Repeater::make('items')
                            ->label('章節內容')
                            ->relationship('items')
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->addActionLabel('新增內容項目')
                            ->schema([
                                Forms\Components\Select::make('type')
                                    ->label('內容類型')
                                    ->options(['text' => '文字', 'component' => '動態元件'])
                                    ->default('text')->live()->required(),
                                Forms\Components\Toggle::make('is_public')
                                    ->label('顯示於前台')
                                    ->default(true)
                                    ->columnSpanFull(),
                                Textarea::make('body_zh_tw')
                                    ->label('內容（中）')->rows(10)->columnSpanFull()
                                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'text'),
                                Textarea::make('body_en')
                                    ->label('內容（英）')->rows(10)->columnSpanFull()
                                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'text'),
                                Forms\Components\TextInput::make('component')
                                    ->label('Livewire 元件名稱')->columnSpanFull()
                                    ->visible(fn (Forms\Get $get): bool => $get('type') === 'component'),
                            ])->columns(2),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('owner_type')
                ->label('類型')
                ->formatStateUsing(fn ($state) => match ($state) {
                    Page::class, 'pages' => '網站頁面',
                    ResearchOutput::class, 'research_outputs' => '研究成果',
                    default => class_basename($state),
                })
                ->badge()
                ->color(fn ($state) => in_array($state, [Page::class, 'pages'], true) ? 'success' : 'info')
                ->sortable(),

            Tables\Columns\TextColumn::make('owner')
                ->label('對應物件')
                ->formatStateUsing(function ($record) {
                    $owner = $record->owner;

                    if (! $owner) {
                        return '-';
                    }

                    return match (true) {
                        $owner instanceof Page
                            => "頁面: {$owner->slug} ({$owner->title_zh_tw})",

                        $owner instanceof ResearchOutput
                            => "成果: {$owner->slug} - {$owner->title_zh_tw}",

                        default
                            => class_basename($record->owner_type) . ': ' . ($owner->name ?? $owner->title ?? $owner->id),
                    };
                })
                ->sortable()
                ->searchable(),

                Tables\Columns\TextColumn::make('title_zh_tw')
                    ->label('標題（中）')
                    ->limit(20)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('公開')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('owner_type')
                    ->label('內容類型')
                    ->options(['pages' => '網站頁面', 'research_outputs' => '研究成果']),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('發布狀態')
                    ->trueLabel('前台顯示')->falseLabel('已隱藏')->placeholder('全部'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('preview')
                    ->label('預覽')->icon('heroicon-o-eye')
                    ->url(fn (ContentBlock $record) => static::getFrontendUrl($record))
                    ->visible(fn (ContentBlock $record) => filled(static::getFrontendUrl($record)))
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
            'index' => Pages\ListContentBlocks::route('/'),
            'create' => Pages\CreateContentBlock::route('/create'),
            'edit' => Pages\EditContentBlock::route('/{record}/edit'),
        ];
    }

    public static function getFrontendUrl(ContentBlock $record): ?string
    {
        $owner = $record->owner;

        return match (true) {
            $owner instanceof Page => url('/' . ltrim($owner->slug, '/')),
            $owner instanceof ResearchOutput => url('/results/' . ltrim($owner->slug, '/')),
            default => null,
        };
    }
}
