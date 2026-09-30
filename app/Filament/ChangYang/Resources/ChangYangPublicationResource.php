<?php

namespace App\Filament\ChangYang\Resources;

use App\Filament\ChangYang\Resources\ChangYangPublicationResource\Pages;
use App\Filament\ChangYang\Support\ChangYangPageLink;
use App\Filament\Resources\PublicationResource as BasePublicationResource;
use App\Models\Web\Publication;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChangYangPublicationResource extends BasePublicationResource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = '學術產出';

    public static function listUrl(): string
    {
        return ChangYangPageLink::tab('publications');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_changyang', true);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('year')->label('年份')->sortable(),
            Tables\Columns\TextColumn::make('type')->label('類型')->formatStateUsing(fn (?string $state): string => Publication::typeLabels('zh-TW')[$state] ?? $state ?? '')->badge(),
            Tables\Columns\TextColumn::make('abbreviated_authors')->label('作者')->searchable()->wrap(),
            Tables\Columns\TextColumn::make('title')->label('標題')->searchable()->limit(70)->wrap(),
            Tables\Columns\TextColumn::make('journal')->label('期刊')->limit(35)->toggleable(isToggledHiddenByDefault: true),
        ])->defaultSort('year', 'desc')
            ->actions([Tables\Actions\EditAction::make()->label('編輯')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChangYangPublications::route('/'),
            'create' => Pages\CreateChangYangPublication::route('/create'),
            'edit' => Pages\EditChangYangPublication::route('/{record}/edit'),
        ];
    }
}
