<?php

namespace App\Filament\Resources;

use App\Filament\Forms\PageBasicFields;
use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Web\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Web\Page;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\PageResource;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = '研究主題';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = '研究主題';
    protected static ?string $modelLabel = '研究主題';
    protected static ?string $pluralModelLabel = '研究主題';

    public static function getNavigationItems(): array
    {
        $items = [];

        try {
            Subject::query()->with('page')->orderBy('sort_order')->orderBy('id')->get()->each(function (Subject $subject) use (&$items): void {
                if (! $subject->page) {
                    return;
                }
                $items[] = NavigationItem::make($subject->short_name_zh_tw ?: $subject->name_zh_tw)
                    ->group('研究主題')->icon('heroicon-o-book-open')->sort($subject->sort_order ?? $subject->id)
                    ->url(PageResource::getUrl('edit', ['record' => $subject->page], isAbsolute: false))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.pages.edit')
                        && (int) request()->route('record') === (int) $subject->page->getKey());
            });
        } catch (\Throwable) {
            // 資料庫尚未就緒時仍保留新增入口。
        }

        $items[] = NavigationItem::make('新增研究主題')
            ->group('研究主題')->icon('heroicon-o-plus-circle')->sort(999)
            ->url(static::getUrl('create'))
            ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.subjects.create'));

        return $items;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('基本資料')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            ...PageBasicFields::make(
                                urlField: Forms\Components\TextInput::make('page_slug')
                                    ->label('頁面網址')
                                    ->prefix(url('/') . '/subjects/')
                                    ->placeholder('seedling')
                                    ->required()
                                    ->rule(function (): \Closure {
                                        return function (string $attribute, mixed $value, \Closure $fail): void {
                                            $tail = ltrim((string) $value, '/');
                                            $slug = str_starts_with($tail, 'subjects/') ? $tail : 'subjects/' . $tail;

                                            if (Page::query()->where('slug', $slug)->exists()) {
                                                $fail('此頁面網址已經被使用。');
                                            }
                                        };
                                    })
                                    ->helperText('只需填最後一段，例如 seedling。頁面公開後請勿隨意修改。'),
                                visibilityField: Forms\Components\Toggle::make('is_active')
                                    ->label('顯示於前台')
                                    ->default(true),
                                titleZhTwField: 'short_name_zh_tw',
                                titleEnField: 'short_name_en',
                            ),
                        ]),
                    ]),

                Forms\Components\Section::make('完整標題')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('name_zh_tw')
                                ->label('完整標題（中）')
                                ->required()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('name_en')
                                ->label('完整標題（英）')
                                ->required()
                                ->maxLength(255),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Subject $record): string => PageResource::getUrl('edit', ['record' => $record->page_id]))
            ->columns([
                Tables\Columns\TextColumn::make('short_name_zh_tw')
                    ->label('研究主題')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('page.slug')
                    ->label('網址')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('公開')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('公開狀態'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('編輯內容')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Subject $record): string => PageResource::getUrl('edit', ['record' => $record->page_id])),
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
        ];
    }
}
