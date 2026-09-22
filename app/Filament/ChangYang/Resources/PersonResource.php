<?php

namespace App\Filament\ChangYang\Resources;

use App\Filament\ChangYang\Resources\PersonResource\Pages;
use App\Filament\ChangYang\Support\ChangYangPageLink;
use App\Forms\Components\ImageFrameEditor;
use App\Forms\Components\PersonContentEditor;
use App\Models\ChangYang\Person;
use App\Models\ChangYang\PersonCategory;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PersonResource extends Resource
{
    protected static ?string $slug = 'people-records';

    protected static ?string $model = Person::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getPeopleListUrl(): string
    {
        return ChangYangPageLink::tab('people');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'roles' => fn ($query) => $query->where('is_current', true)->with('category'),
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')
                    ->label('姓名')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateHydrated(fn (Forms\Get $get, Forms\Set $set) => $set('image_alt', $get('name')))
                    ->afterStateUpdated(fn (mixed $state, Forms\Set $set) => $set('image_alt', $state)),
                Forms\Components\Toggle::make('is_active')->label('顯示於人物頁')->default(true),
            ]),
            Repeater::make('roles')
                ->label('角色紀錄')
                ->relationship()
                ->addActionLabel('新增角色')
                ->reorderable(false)
                ->schema([
                    Forms\Components\Grid::make(12)->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('身分類型')
                            ->options(fn (): array => PersonCategory::query()->orderBy('sort_order')->pluck('title', 'id')->all())
                            ->live()
                            ->required()
                            ->columnSpan(4),
                        Forms\Components\DatePicker::make('started_on')->label('開始日期')->columnSpan(3),
                        Forms\Components\DatePicker::make('ended_on')->label('結束日期')->columnSpan(3),
                        Forms\Components\Toggle::make('is_current')->label('顯示')->default(true)->columnSpan(2),
                    ]),
                ]),
            Forms\Components\FileUpload::make('image_path')
                ->label('照片')->disk('public')->directory('changyang/people')->visibility('public')
                ->image()->previewable(false)->live(),
            Forms\Components\TextInput::make('image_alt')->hidden()->dehydratedWhenHidden(),
            PersonContentEditor::make('contact_html')
                ->label('職稱與聯絡資訊')
                ->live(debounce: 750)
                ->columnSpanFull()
                ->visible(fn (Forms\Get $get): bool => static::hasPiRole($get('roles') ?? [])),
            PersonContentEditor::make('introduction_html')
                ->label('人物介紹')
                ->live(debounce: 750)
                ->columnSpanFull(),
            ImageFrameEditor::make('display_settings')
                ->label('照片取樣與預覽')
                ->imagePath(fn (Forms\Get $get): mixed => $get('image_path'))
                ->previewData(fn (Forms\Get $get): array => [
                    'heading' => $get('name'),
                    'content' => $get('introduction_html'),
                    'contact' => $get('contact_html'),
                    'layout' => 'image_left',
                ])
                ->columnSpanFull(),
        ]);
    }

    protected static function hasPiRole(array $roles): bool
    {
        $piCategoryIds = PersonCategory::query()
            ->where('title', 'like', '%PI%')
            ->pluck('id')
            ->all();

        return collect($roles)
            ->contains(fn (array $role): bool => in_array((int) ($role['category_id'] ?? 0), $piCategoryIds, true));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->label('照片')->disk('public')->square(),
                Tables\Columns\TextColumn::make('name')->label('姓名')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.category.title')->label('目前類型')->badge()->separator(', '),
                Tables\Columns\IconColumn::make('is_active')->label('顯示')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([Tables\Actions\CreateAction::make()->label('新增人物')])
            ->actions([Tables\Actions\EditAction::make()->label('編輯')]);
    }

    public static function getPages(): array
    {
        return [
            'create' => Pages\CreatePerson::route('/create'),
            'edit' => Pages\EditPerson::route('/{record}/edit'),
        ];
    }
}
