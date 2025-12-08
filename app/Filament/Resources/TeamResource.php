<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Models\Web\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = '內容管理';
    protected static ?string $navigationLabel = '參與團隊';
    protected static ?string $modelLabel = '參與團隊';
    protected static ?string $pluralModelLabel = '參與團隊';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ====== 基本資訊 ======
                Forms\Components\Section::make('基本資訊')
                    ->schema([

                        Forms\Components\Grid::make(3)->schema([

                            Forms\Components\Select::make('team_type')
                                ->label('團隊類型')
                                ->required()
                                ->options([
                                    'academic'   => '學術機構',
                                    'government' => '政府單位',
                                    'other'      => '其他',
                                ])
                                ->native(false),

                            Forms\Components\Toggle::make('is_active')
                                ->label('是否顯示於前台')
                                ->default(true),

                            Forms\Components\TextInput::make('website_url')
                                ->label('官方網站')
                                ->url()
                                ->maxLength(255)
                                ->columnSpan(1),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('institution_zh_tw')
                                ->label('所屬學校 / 機構（中）')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('institution_en')
                                ->label('所屬學校 / 機構（英）')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('department_zh_tw')
                                ->label('系所 / 單位（中）')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('department_en')
                                ->label('系所 / 單位（英）')
                                ->maxLength(255),
                        ]),

                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('pi_name_zh_tw')
                                ->label('負責人（中）')
                                ->maxLength(255)
                                ->helperText('政府單位可留空'),

                            Forms\Components\TextInput::make('pi_name_en')
                                ->label('負責人（英）')
                                ->maxLength(255),
                        ]),

                    ])->columns(1),

                // ====== 團隊介紹 ======
                Forms\Components\Section::make('團隊介紹')
                    ->schema([

                        Forms\Components\RichEditor::make('description_zh_tw')
                            ->label('團隊介紹（中）')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('teams'),

                        Forms\Components\RichEditor::make('description_en')
                            ->label('團隊介紹（英）')
                            ->columnSpanFull()
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('teams'),
                    ]),

                // ====== Logo ======
                Forms\Components\Section::make('Logo')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo 圖檔')
                            ->image()
                            ->directory('teams/logo')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->columnSpan(1),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('team_type')
                    ->label('類別')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'academic' => '學術機構',
                        'government' => '政府單位',
                        'other' => '其他',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('institution_zh_tw')
                    ->label('所屬機構')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pi_name_zh_tw')
                    ->label('負責人（中）')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('顯示')
                    ->boolean(),

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
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
