<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages;
use App\Models\Web\Team;
use App\Filament\Forms\ImmediatePublicImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = '關於我們';
    protected static ?string $navigationLabel = '研究團隊';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = '研究團隊';
    protected static ?string $pluralModelLabel = '研究團隊';

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
                                ->label('顯示於前台')
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
                        ImmediatePublicImage::field('logo_path', 'Logo 圖檔', directory: 'teams/logo', maxSize: 2048)
                            ->live()
                            ->afterStateHydrated(fn (Forms\Components\FileUpload $component): Forms\Components\FileUpload => $component->state([]))
                            ->afterStateUpdated(function (Forms\Components\FileUpload $component, mixed $state, ?Team $record): void {
                                $upload = ImmediatePublicImage::upload($state);

                                if (! $upload || ! $record) {
                                    return;
                                }

                                $path = ImmediatePublicImage::replace($upload, 'teams/logo', $record->logo_path);
                                $record->update(['logo_path' => $path]);
                                $component->state([]);
                            })
                            ->helperText('選擇檔案後會立即上傳；重新選擇會直接取代舊 Logo。'),
                        Forms\Components\Placeholder::make('logo_preview')
                            ->label('目前 Logo')
                            ->content(fn (?Team $record): HtmlString => ImmediatePublicImage::preview($record?->logo_path, '尚未上傳 Logo', circular: true)),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('deleteLogo')
                                ->label('刪除 Logo')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->action(function (Forms\Set $set, ?Team $record): void {
                                    if (! $record) {
                                        return;
                                    }

                                    ImmediatePublicImage::delete($record->logo_path);
                                    $record->update(['logo_path' => null]);
                                    $set('logo_path', []);
                                }),
                        ])->visible(fn (?Team $record): bool => filled($record?->logo_path)),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('team_type')
                    ->label('類別')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('institution_zh_tw')
                    ->label('所屬機構')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('department_zh_tw')
                    ->label('系所單位')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pi_name_zh_tw')
                    ->label('負責人（中）')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('公開')
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
