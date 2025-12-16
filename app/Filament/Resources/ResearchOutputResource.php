<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResearchOutputResource\Pages;
use App\Filament\Resources\ResearchOutputResource\RelationManagers;
use App\Models\Web\ResearchOutput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResearchOutputResource extends Resource
{
    protected static ?string $model = ResearchOutput::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = '內容管理';
    protected static ?string $navigationLabel = '研究成果';
    protected static ?string $modelLabel = '研究成果';
    protected static ?string $pluralModelLabel = '研究成果';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本設定')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->maxLength(150)
                                ->unique(ignoreRecord: true)
                                ->helperText('例如：fushan-forest-composition (不用加results/)'),
                            Forms\Components\Toggle::make('is_public')
                                ->label('公開')
                                ->default(true),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('title_zh_tw')
                                ->label('標題（中）')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('title_en')
                                ->label('標題（英）')
                                ->maxLength(255),

                            ]),
                            Forms\Components\Grid::make(2)->schema([
                            
                            Forms\Components\TextInput::make('view')
                                ->label('插入view (chat)')
                                ->maxLength(100),
                            Forms\Components\KeyValue::make('params')
                                ->label('參數設定')
                                ->keyLabel('參數名稱')
                                ->valueLabel('參數內容')
                                ->reorderable(),
                        ])->columns(2),
                        ]),

                Forms\Components\Section::make('關聯')
                    ->schema([
                        Forms\Components\Select::make('subjects')
                            ->label('研究主題（可複選）')
                            ->relationship('subjects', 'name_zh_tw')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('可選多個主題，例如：幼苗動態、種子雨'),

                        Forms\Components\Select::make('sites')
                            ->label('樣區（可複選）')
                            ->relationship('sites', 'name_zh_tw')  // ← 這裡換成你 Site 真正的欄位
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('可選多個樣區，例如：福山、南仁山'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('內容')
                    ->schema([
                        Textarea::make('body_zh_tw')
                            ->label('內容（中）')
                            ->rows(10)
                            ->columnSpanFull(),


                        // 英文內容（RichEditor + HTML）
                        Textarea::make('body_en')
                            ->label('內容（英）')
                            ->rows(10)
                            ->columnSpanFull(),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title_zh_tw')
                    ->label('標題（中）')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sites.name_zh_tw') // ← 改成你的欄位
                    ->label('樣區')
                    ->badge()
                    ->separator(', '),

                Tables\Columns\TextColumn::make('subjects.name_zh_tw')
                    ->label('主題')
                    ->badge()
                    ->separator(', '),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('公開')
                    ->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResearchOutputs::route('/'),
            'create' => Pages\CreateResearchOutput::route('/create'),
            'edit' => Pages\EditResearchOutput::route('/{record}/edit'),
        ];
    }
}
