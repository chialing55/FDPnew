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
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Select;
use App\Filament\Resources\SiteResource\RelationManagers\SiteTeamsRelationManager;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = '內容管理'; // 看你要放哪一組
    protected static ?string $navigationLabel = '樣區';
    protected static ?string $modelLabel = '樣區';
    protected static ?string $pluralModelLabel = '樣區';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本資訊')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Select::make('page_id')
                                ->label('對應頁面（Slug）')
                                ->required()
                                ->options(function (?Site $record) {
                                    // 取出已經被其它 site 使用的 page_id（排除目前正在編輯的這筆）
                                    $usedPageIds = Site::query()
                                        ->when($record?->id, fn ($q) => $q->where('id', '!=', $record->id))
                                        ->pluck('page_id')
                                        ->filter()
                                        ->all();

                                    return Page::query()
                                        ->where('nav_group', 'plots')
                                        ->when($usedPageIds, fn ($q) => $q->whereNotIn('id', $usedPageIds))
                                        ->orderBy('nav_order')
                                        ->get()
                                        ->mapWithKeys(function (Page $page) {
                                            // 下拉顯示：slug - 中文標題
                                            return [
                                                $page->id => $page->slug . ' - ' . $page->title_zh_tw,
                                            ];
                                        });
                                })
                                ->searchable()
                                ->native(false)  // 使用 Filament 的美化選單
                                ->helperText('從 plots 群組的頁面中選擇一個 slug 對應這個樣區')
                                // 確保每個 page_id 只被一個 site 用
                                ->unique(ignoreRecord: true, column: 'page_id')
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $pageId = $get('page_id');
                                    if (! $pageId) {
                                        return;
                                    }

                                    $page = Page::find($pageId);

                                    if ($page) {
                                        // 自動填入樣區名稱
                                        $set('name_zh_tw', $page->title_zh_tw);
                                        $set('name_en', $page->title_en);
                                    }
                                }),
                        ]), 
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
                        Forms\Components\Grid::make(2)->schema([ 
                            Forms\Components\Textarea::make('description_zh_tw')
                                ->label('樣區簡介（中）')
                                ->rows(5),

                            Forms\Components\Textarea::make('description_en')
                                ->label('樣區簡介（英）')
                                ->rows(5),
                        ]), 
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
