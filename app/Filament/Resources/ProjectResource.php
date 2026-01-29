<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Web\Project;
use App\Models\Web\Site;
use App\Models\Web\Subject;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;

use Filament\Forms\Get;
use Filament\Forms\Set;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = '內容管理';
    protected static ?string $navigationLabel = '研究計畫';
    protected static ?string $modelLabel = '研究計畫';
    protected static ?string $pluralModelLabel = '研究計畫';

    public static function form(Form $form): Form
    {
        // 取得 Other subject 的 id（如果沒有就回傳 null）

        return $form
            ->schema([

                // ====== 基本資訊 ======
                Forms\Components\Section::make('基本資訊')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('code')
                                ->label('計畫代碼 code')
                                ->maxLength(100)
                                ->helperText('可留空；用於內部識別或排序'),

                            Forms\Components\Toggle::make('is_active')
                                ->label('是否顯示')
                                ->default(true),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title_zh_tw')
                                ->label('計畫名稱（中）')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('title_en')
                                ->label('計畫名稱（英）')
                                ->required()
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('pi_zh_tw')
                                ->label('主持人（中）')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('pi_en')
                                ->label('PI（英）')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DatePicker::make('start_date')
                                ->label('開始日期')
                                ->native(false),

                            Forms\Components\DatePicker::make('end_date')
                                ->label('結束日期')
                                ->native(false),
                        ]),

                Forms\Components\Grid::make(2)->schema([

                    Forms\Components\Select::make('funding_agency_zh_tw')
                        ->label('補助單位（中）')
                        ->options([
                            '林業試驗所' => '林業試驗所',
                            '林業及自然保育署' => '林業及自然保育署',
                            '國家科學及技術委員會' => '國家科學及技術委員會',
                            'Smithsonian Tropical Research Institute' => 'Smithsonian Tropical Research Institute',
                        ])
                        ->searchable()
                        ->preload()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            $map = [
                                '林業試驗所' => 'Taiwan Forestry Research Institute',
                                '林業及自然保育署' => 'Forestry and Nature Conservation Agency',
                                '國家科學及技術委員會' => 'National Science and Technology Council',
                                'Smithsonian Tropical Research Institute' => 'Smithsonian Tropical Research Institute',
                            ];

                            $set('funding_agency_en', $map[$state] ?? null);
                        }),

                    Forms\Components\TextInput::make('funding_agency_en')
                        ->label('Funding agency（英）')
                        ->maxLength(255)
                        ->readOnly(),
                ]),


                        Forms\Components\TextInput::make('website_url')
                            ->label('計畫網站 URL')
                            ->maxLength(255)
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // ====== 計畫摘要 ======
                Forms\Components\Section::make('計畫摘要')
                    ->schema([
                        Forms\Components\Textarea::make('summary_zh_tw')
                            ->label('摘要（中）')
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('summary_en')
                            ->label('摘要（英）')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),

                // ====== 關聯 Sites / Subjects ======
                Forms\Components\Section::make('關聯設定')
                    ->schema([

                        Forms\Components\Grid::make(2)->schema([

                            Forms\Components\Select::make('sites')
                                ->label('關聯樣區 Sites（可多選）')
                                ->multiple()
                                ->relationship('sites', 'name_zh_tw')
                                ->searchable()
                                ->preload()
                                ->native(false),

                            Forms\Components\Select::make('subjects')
                                ->label('研究主題 Subjects（可多選）')
                                ->multiple()
                                ->relationship('subjects', 'name_zh_tw')
                                ->searchable()
                                ->preload()
                                ->native(false),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('subject_other_zh_tw')
                                ->label('其他主題（中文）')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('subject_other_en')
                                ->label('其他主題（英文）')
                                ->maxLength(255),
                        ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('代碼')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title_zh_tw')
                    ->label('計畫名稱（中）')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('顯示')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sites.name_zh_tw')
                    ->label('Sites')
                    ->badge()
                    ->separator(',')
                    ->limit(3),

                // Subjects（含 other）
                Tables\Columns\TextColumn::make('subjects_display')
                    ->label('Subjects')
                    ->state(function (Project $record) {
                        $names = $record->subjects
                            ->pluck('name_zh_tw')   // 依你的欄位：name_zh_tw / title_zh_tw
                            ->filter()
                            ->values()
                            ->all();

                        if (! empty($record->subject_other_zh_tw)) {
                            $names[] = '其他：' . $record->subject_other_zh_tw;
                        }

                        return implode('、', $names);
                    })
                    ->wrap()
                    ->toggleable(),


                Tables\Columns\TextColumn::make('start_date')
                    ->label('起始時間')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('是否顯示'),
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
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
