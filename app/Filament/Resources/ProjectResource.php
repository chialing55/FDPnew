<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Forms\ContentBlockForm;
use App\Filament\Forms\ContentRelationForm;
use App\Filament\Forms\PageBasicFields;
use App\Models\Web\Project;
use Filament\Forms;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = '研究成果';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = '研究計畫';
    protected static ?string $modelLabel = '研究計畫';
    protected static ?string $pluralModelLabel = '研究計畫';

    public static function form(Form $form): Form
    {
        if ($form->getOperation() === 'create') {
            return $form->schema([
                Forms\Components\Section::make('基本資料')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        ...PageBasicFields::make(
                            titleZhTwLabel: '計畫名稱（中）',
                            titleEnLabel: '計畫名稱（英）',
                        ),
                    ]),
                ]),
            ]);
        }

        return $form->schema([
            Tabs::make('研究計畫設定')->tabs([
                Tabs\Tab::make('基本資料')->icon('heroicon-o-document-text')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        ...PageBasicFields::make(
                            titleZhTwLabel: '計畫名稱（中）',
                            titleEnLabel: '計畫名稱（英）',
                        ),
                    ]),
                    Forms\Components\Section::make('其他設定')->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('code')->label('計畫代碼')->maxLength(100),
                            Forms\Components\TextInput::make('website_url')->label('計畫網站 URL')->url()->maxLength(255),
                            Forms\Components\TextInput::make('pi_zh_tw')->label('主持人（中）')->maxLength(255),
                            Forms\Components\TextInput::make('pi_en')->label('PI（英）')->maxLength(255),
                            Forms\Components\DatePicker::make('start_date')->label('開始日期')->native(false),
                            Forms\Components\DatePicker::make('end_date')->label('結束日期')->native(false),
                            Forms\Components\TextInput::make('funding_agency_zh_tw')->label('補助單位（中）')->maxLength(255),
                            Forms\Components\TextInput::make('funding_agency_en')->label('Funding agency（英）')->maxLength(255),
                        ]),
                    ]),
                ]),
                Tabs\Tab::make('關聯設定')->icon('heroicon-o-link')->schema([
                    ...ContentRelationForm::fields(siteLabel: '關聯樣區（可複選）'),
                    Forms\Components\TextInput::make('subject_other_zh_tw')->label('其他主題（中文）'),
                    Forms\Components\TextInput::make('subject_other_en')->label('Other subject (English)'),
                ])->columns(2),
                Tabs\Tab::make('頁面內容')->icon('heroicon-o-rectangle-stack')->schema([
                    Forms\Components\Placeholder::make('project_content_notice')
                        ->label('研究計畫內容區塊說明')
                        ->content('研究計畫頁面需建立「計畫摘要 / Project Summary」內容區塊。'),
                    ContentBlockForm::make(),
                ]),
            ])->persistTabInQueryString()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->recordUrl(fn (Project $record): string => static::getUrl('edit', ['record' => $record]))
            ->columns([
                Tables\Columns\TextColumn::make('title_zh_tw')->label('計畫名稱')->searchable()->sortable()->wrap(),
                Tables\Columns\TextColumn::make('code')->label('計畫代碼')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('sites.name_zh_tw')->label('樣區')->badge()->separator(', '),
                Tables\Columns\TextColumn::make('subjects.name_zh_tw')->label('研究主題')->badge()->separator(', '),
                Tables\Columns\TextColumn::make('start_date')->label('開始日期')->date('Y-m-d')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('公開')->boolean(),
            ])->defaultSort('id', 'desc')
            ->actions([Tables\Actions\EditAction::make()->label('編輯內容')])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProjects::route('/'), 'create' => Pages\CreateProject::route('/create'), 'edit' => Pages\EditProject::route('/{record}/edit')];
    }
}
