<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Web\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = '頁面';
    protected static ?string $pluralModelLabel = '頁面';
    protected static ?string $modelLabel = '頁面';
    protected static ?string $navigationGroup = '內容管理';

    // 目前先只給 super-admin / data-editor 管理頁面
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Filament::auth()->user();

        return $user?->hasAnyRole('super-admin', 'data-editor') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本資訊')
                    ->schema([
                    // 第 1 行：slug（獨佔一行）
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('例如：plots/fushan、about/team'),
                    ]),

                    // 第 2 行：中英文標題並排
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title_zh_tw')
                            ->label('標題（中）')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title_en')
                            ->label('標題（英）')
                            ->required()
                            ->maxLength(255),
                    ]),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('nav_group')
                            ->label('導覽群組')
                            ->options([
                                'about' => '關於我們（about）',
                                'sites'      => '動態樣區（sites）',
                                'subjects'   => '研究主題（subjects）',
                                'results'    => '研究成果（results）',
                                'others'     => '其他頁面（others）',
                            ])
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nav_order')
                            ->label('導覽排序')
                            ->numeric()
                            ->minValue(1)
                            ->columnSpan(1),
                    ]),

                    // 第 3 行：剩下的資料（你可以再細分）
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('view')
                            ->label('模板 view')
                            ->columnSpan(1)
                            ->maxLength(150)
                            ->helperText('選填：若有對應特殊頁面，例如 pages.web.splist'),

                        Forms\Components\Textarea::make('description')
                            ->label('描述')
                            ->rows(3)
                            ->columnSpan(1),

                        Forms\Components\FileUpload::make('hero_image')
                            ->label('Hero 圖片')
                            ->image()
                            ->directory('hero')
                            ->columnSpan(1),
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
                    ->searchable(),

                Tables\Columns\TextColumn::make('title_zh_tw')
                    ->label('標題（中）')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title_en')
                    ->label('標題（英）')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nav_group')
                    ->label('導覽群組代碼')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nav_order')
                    ->label('導覽排序')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('view')
                    ->label('模板 (view)')
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
