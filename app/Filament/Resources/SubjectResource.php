<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Web\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Web\Page;
use Filament\Forms\Set;
use Filament\Forms\Get;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = '內容管理';
    protected static ?string $navigationLabel = '研究主題';
    protected static ?string $modelLabel = '研究主題';
    protected static ?string $pluralModelLabel = '研究主題';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ====== 基本資訊 ======
                Forms\Components\Section::make('基本資訊')
                    ->schema([
                        // slug 一行獨占
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('slug')
                                ->label('對應頁面（Slug）')
                                ->required()
                                ->options(function () {
                                    $usedSlugs = Subject::pluck('slug')->toArray();
                                    return Page::query()
                                        ->where('nav_group', 'subjects')
                                        ->whereNotIn('slug', $usedSlugs)
                                        ->orderBy('slug')
                                        ->get()
                                        ->mapWithKeys(fn ($page) => [
                                            $page->slug => $page->slug . ' - ' . $page->title_zh_tw,
                                        ]);
                                })
                                ->searchable()
                                ->native(false)
                                ->helperText('從 plots 群組的頁面中選擇一個 slug 對應這個樣區')
                                ->unique(ignoreRecord: true)
                                ->live()   // ★ 一定要有，選完 slug 才會觸發更新
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $slug = $get('slug');   // 也可以用傳進來的 $state，但這樣最穩

                                    if (! $slug) {
                                        return;
                                    }

                                    $page = Page::where('slug', $slug)->first();

                                    if (! $page) {
                                        return;
                                    }

                                    // 若你不想覆蓋使用者已經打好的內容，可以先檢查 $get(...)
                                    $set('short_name_zh_tw', $page->title_zh_tw);
                                    $set('short_name_en',   $page->title_en);
                                    $set('name_zh_tw',      $page->title_zh_tw);
                                    $set('name_en',         $page->title_en);
                                }),                               
                        ]),


                        // 中英文主題名稱
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name_zh_tw')
                                ->label('主題名稱（中）')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('name_en')
                                ->label('主題名稱（英）')
                                ->required()
                                ->maxLength(255),
                        ]),

                        // 中英文短名
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('short_name_zh_tw')
                                ->label('短名（中）')
                                ->maxLength(100),

                            Forms\Components\TextInput::make('short_name_en')
                                ->label('短名（英）')
                                ->maxLength(100),
                        ]),

                        // 是否顯示 + 排序
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Toggle::make('is_active')
                                ->label('是否顯示')
                                ->default(true),

                            Forms\Components\TextInput::make('sort_order')
                                ->label('排序用')
                                ->numeric()
                                ->default(0)
                                ->helperText('數字越小排序越前面'),
                        ]),
                    ])
                    ->columns(2),

                // ====== 主題簡介 ======
                Forms\Components\Section::make('主題簡介')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Textarea::make('description_zh_tw')
                                    ->label('主題簡介（中）')
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('description_en')
                                    ->label('主題簡介（英）')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // ====== 研究方法 ======
                Forms\Components\Section::make('研究方法說明')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Textarea::make('method_zh_tw')
                                    ->label('研究方法說明（中）')
                                    ->rows(6)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('method_en')
                                    ->label('研究方法說明（英）')
                                    ->rows(6)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name_zh_tw')
                    ->label('主題名稱（中）')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('short_name_zh_tw')
                    ->label('短名（中）')
                    ->toggleable()
                    ->limit(20),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('顯示')
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
            'index' => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit' => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}
