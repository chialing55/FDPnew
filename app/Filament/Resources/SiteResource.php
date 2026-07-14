<?php

namespace App\Filament\Resources;

use App\Filament\Forms\PageBasicFields;
use App\Filament\Resources\SiteResource\Pages;
use App\Models\Web\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Web\Page;
use Filament\Navigation\NavigationItem;
use App\Filament\Resources\PageResource;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = '首頁';
    protected static ?string $navigationLabel = '樣區介紹';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = '動態樣區';
    protected static ?string $pluralModelLabel = '動態樣區';

    public static function getNavigationItems(): array
    {
        $items = [];

        try {
            Site::query()->with('page')->orderBy('sort_order')->orderBy('id')->get()->each(function (Site $site) use (&$items): void {
                if (! $site->page) {
                    return;
                }
                $items[] = NavigationItem::make($site->short_name_zh_tw ?: $site->name_zh_tw)
                    ->group('動態樣區')->icon('heroicon-o-map-pin')->sort($site->sort_order ?? $site->id)
                    ->url(PageResource::getUrl('edit', ['record' => $site->page], isAbsolute: false))
                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.pages.edit')
                        && (int) request()->route('record') === (int) $site->page->getKey());
            });
        } catch (\Throwable) {
            // 資料庫尚未就緒時仍保留管理入口。
        }

        $items[] = NavigationItem::make('新增樣區')
            ->group('動態樣區')->icon('heroicon-o-plus-circle')->sort(999)
            ->url(static::getUrl('create'))
            ->isActiveWhen(fn (): bool => request()->routeIs('filament.cms.resources.sites.create'));

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
                                    ->prefix(url('/') . '/sites/')
                                    ->placeholder('fushan')
                                    ->required()
                                    ->rule(function (): \Closure {
                                        return function (string $attribute, mixed $value, \Closure $fail): void {
                                            $tail = ltrim((string) $value, '/');
                                            $slug = str_starts_with($tail, 'sites/') ? $tail : 'sites/' . $tail;

                                            if (Page::query()->where('slug', $slug)->exists()) {
                                                $fail('此頁面網址已經被使用。');
                                            }
                                        };
                                    })
                                    ->helperText('只需填最後一段，例如 fushan。頁面公開後請勿隨意修改。'),
                                visibilityField: Forms\Components\Toggle::make('is_active')
                                    ->label('顯示於前台')
                                    ->default(true),
                                titleZhTwField: 'name_zh_tw',
                                titleEnField: 'name_en',
                            ),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Site $record): string => PageResource::getUrl('edit', ['record' => $record->page_id]))
            ->columns([
                Tables\Columns\TextColumn::make('page.title_zh_tw')
                    ->label('動態樣區')
                    ->sortable()
                    ->searchable()
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
                    ->url(fn (Site $record): string => PageResource::getUrl('edit', ['record' => $record->page_id])),
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
        ];
    }

}
