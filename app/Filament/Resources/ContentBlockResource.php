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
        return $form
            ->schema([
                // ====== 基本設定 ======
                Forms\Components\Section::make('基本設定')
                    ->schema([
                        // owner_type + owner_id
                        Forms\Components\Grid::make(2)->schema([

                            // 1. owner_type 選單
                            Forms\Components\Select::make('owner_type')
                                ->label('所屬類型')
                                ->required()
                                ->options([
                                    'site'   => '樣區（sites）',
                                    'topic'  => '研究主題（topics）',
                                    'pages'  => '頁面（pages）',
                                ])
                                ->native(false)
                                ->live(), // ★ 變更時刷新 owner_id

                            // 2. owner_id 會依 owner_type 改選項
                            Forms\Components\Select::make('owner_id')
                                ->label('所屬對象')
                                ->required()
                                ->options(function (Get $get) {
                                    $type = $get('owner_type');

                                    if (! $type) {
                                        return [];
                                    }

                                    return match ($type) {
                                        'site' => Site::query()
                                            ->orderBy('slug')
                                            ->get()
                                            ->mapWithKeys(fn ($site) => [
                                                $site->id => 'site: ' . $site->slug . ' - ' . $site->name_zh_tw,
                                            ]),

                                        'topic' => Subject::query()
                                            ->orderBy('slug')
                                            ->get()
                                            ->mapWithKeys(fn ($topic) => [
                                                $topic->id => 'topic: ' . $topic->slug . ' - ' . $topic->name_zh_tw,
                                            ]),

                                        'pages' => Page::query()
                                            ->orderBy('slug')
                                            ->get()
                                            ->mapWithKeys(fn ($page) => [
                                                $page->id => 'page: ' . $page->slug . ' - ' . $page->title_zh_tw,
                                            ]),

                                        default => [],
                                    };
                                })
                                ->searchable()
                                ->native(false)
                                ->disabled(fn (Get $get) => ! $get('owner_type'))
                                ->hint('先選擇所屬類型，再從這裡選擇對象'),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('block_type')
                                ->label('區塊類型')
                                ->required()
                                ->maxLength(50)
                                ->helperText('例如 environment、climate、intro、method_detail'),

                            Forms\Components\TextInput::make('sort_order')
                                ->label('區塊排序')
                                ->numeric()
                                ->default(0),

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

                            // 中文內容
                            RichEditor::make('body_zh_tw')
                                ->label('內容（中）')
                                ->columnSpanFull()
                                ->fileAttachmentsDisk('public')                 // 存在 storage/app/public
                                ->fileAttachmentsDirectory(function (Get $get) {
                                    return 'content_blocks/' . $get('owner_type') . '/' . $get('owner_id');
                                }),                 // 動態目錄

                            // 英文內容
                            RichEditor::make('body_en')
                                ->label('內容（英）')
                                ->columnSpanFull()
                                ->fileAttachmentsDisk('public')
                                ->fileAttachmentsDirectory(function (Get $get) {
                                    return 'content_blocks/' . $get('owner_type') . '/' . $get('owner_id');
                                }), // 動態目錄

                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner_type')
                    ->label('類型')
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner_id')
                    ->label('對象ID')
                    ->sortable(),

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
