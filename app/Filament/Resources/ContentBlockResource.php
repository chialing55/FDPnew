<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentBlockResource\Pages;
use App\Models\Web\ContentBlock;
use App\Models\Web\Site;
use App\Models\Web\Subject;
use App\Models\Web\Page;
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
                // ====== 基本設定 ======
                Forms\Components\Section::make('基本設定')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('page_id')
                                ->label('對應頁面（Slug）')
                                ->relationship(
                                    name: 'page',
                                    titleAttribute: 'slug',
                                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('slug'),
                                )
                                ->getOptionLabelFromRecordUsing(
                                    fn (Page $record) => $record->slug . ' - ' . $record->title_zh_tw
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('從頁面中選擇一個 slug 對應這個內容區塊'),
                        ]),


                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('block_type')
                                ->label('區塊類型')
                                ->required()
                                ->options([
                                    'intro'     => '簡介區塊（intro）',
                                    'content'   => '一般內容（content）',
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
                            Forms\Components\TextInput::make('view')
                                ->label('插入view')
                                ->maxLength(100),
                            Forms\Components\Toggle::make('is_public')
                                ->label('是否顯示於前台')
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
                Tables\Columns\TextColumn::make('page.slug')
                    ->label('頁面 Slug')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('page.title_zh_tw')
                    ->label('頁面標題')
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
