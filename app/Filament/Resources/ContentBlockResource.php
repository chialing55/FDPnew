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
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\RichEditor;
use Wiebenieuwenhuis\FilamentCodeEditor\Components\CodeEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Filament\Forms\Set;

class ContentBlockResource extends Resource
{
    protected static ?string $model = ContentBlock::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = '內容管理';
    protected static ?string $navigationLabel = '內容區塊';
    protected static ?string $modelLabel = '內容區塊';
    protected static ?string $pluralModelLabel = '內容區塊';

    public static function form(Form $form): Form
    {
/** @var \Illuminate\Contracts\Support\Htmlable $imageExampleView */
$imageExampleView = View::make('filament.partials.image_example');

        return $form
            ->schema([
                Forms\Components\Hidden::make('owner_type'),
                Forms\Components\Hidden::make('owner_id'),
                // ====== 基本設定 ======
                Forms\Components\Section::make('基本設定')
                    ->schema([
Forms\Components\Select::make('owner_selector')
    ->label('對應物件（頁面或成果）')
    ->helperText('從 Page 或 ResearchOutput 中選擇一個作為這個內容區塊的擁有者')
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


                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('block_type')
                                ->label('區塊類型')
                                ->required()
                                ->options([
                                    'intro'     => '簡介區塊（intro）',
                                    'content'   => '一般內容（content）',
                                    'view'      => '自訂view區塊（view）',
                                    'gallery'   => '相片區塊（gallery）',
                                    'map'       => '地圖區塊（map）',
                                    'stats'     => '統計數據區塊（stats）',
                                    'quote'     => '重點引言（quote）',
                                    'table'     => '表格內容（table）',
                                    'download'  => '附件下載（download）',
                                ]),
                                

                            Forms\Components\TextInput::make('sort_order')
                                ->label('區塊排序')
                                ->numeric()
                                ->default(0),
                            Forms\Components\Toggle::make('is_public')
                                ->label('是否顯示於前台')
                                ->default(true),
                            Forms\Components\TextInput::make('view')
                                ->label('插入view')
                                ->maxLength(100),
                            Forms\Components\KeyValue::make('params')
                                ->label('參數設定')
                                ->keyLabel('參數名稱')
                                ->valueLabel('參數內容')
                                ->reorderable()
                            
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

                        Forms\Components\Grid::make()
                            ->schema([
                                Textarea::make('body_zh_tw')
                                    ->label('內容（中）')
                                    ->rows(10)
                                    ->columnSpanFull()
                                    ->helperText($imageExampleView),


                                // 英文內容（RichEditor + HTML）
                                Textarea::make('body_en')
                                    ->label('內容（英）')
                                    ->rows(10)
                                    ->columnSpanFull(),
                        ]),
                        Forms\Components\Grid::make()
                            ->schema([
                            Textarea::make('attachments_preview')
                                ->label('已上傳圖片路徑')
                                ->disabled()
                                ->dehydrated(false) // 不寫回資料庫
                                ->rows(4)
                                ->formatStateUsing(function ($state, Get $get) {
                                    $files = $get('attachments') ?? [];

                                    if (! is_array($files)) {
                                        return '';
                                    }

                                    return collect($files)
                                        ->map(fn ($path) => '/storage/' . ltrim($path, '/'))
                                        ->implode("\n");
                                })
                                ->helperText('複製上方路徑，貼到內文中作為 <img src="..."> 使用。')
                                ->columnSpanFull(),
                        ]),

                        Forms\Components\Grid::make()
                            ->schema([
                            Forms\Components\FileUpload::make('attachments')
                                ->label('附加圖片')
                                ->disk('public')
                                ->directory(function (?ContentBlock $record, Get $get) {
                                    if ($record && $record->id) {
                                        return "content_blocks/{$record->id}";
                                    }
                                    return 'content_blocks/pending';
                                })
                                ->image()
                                ->multiple()
                                ->preserveFilenames()
                                ->imagePreviewHeight('150')
                                ->panelLayout('grid')
                                ->enableOpen()
                                ->helperText('可上傳多張圖片，儲存後可使用 /storage/... 路徑插入到內文中')
                                ->deleteUploadedFileUsing(function (string $file) {
                                    Storage::disk('public')->delete($file);
                                })
                                // ⭐ 關鍵：attachments 變動時，順便更新 attachments_preview
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $files = $state ?? [];

                                    if (! is_array($files)) {
                                        $files = [];
                                    }

                                    $text = collect($files)
                                        ->map(fn ($path) => '/storage/' . ltrim($path, '/'))
                                        ->implode("\n");

                                    $set('attachments_preview', $text);
                                })
                                ->columnSpanFull(),

                            ]),
  
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('id')
                ->sortable(),

            Tables\Columns\TextColumn::make('owner_type')
                ->label('類型')
                ->formatStateUsing(fn ($state) => match ($state) {
                    Page::class => '頁面 Page',
                    ResearchOutput::class => '成果 Result',
                    default => class_basename($state),
                })
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

                Tables\Columns\TextColumn::make('block_type')
                    ->label('區塊類型')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title_zh_tw')
                    ->label('標題（中）')
                    ->limit(20)
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('前台顯示')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新時間')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
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
            'index' => Pages\ListContentBlocks::route('/'),
            'create' => Pages\CreateContentBlock::route('/create'),
            'edit' => Pages\EditContentBlock::route('/{record}/edit'),
        ];
    }
}
