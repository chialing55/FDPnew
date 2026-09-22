<?php

namespace App\Filament\ChangYang\Resources;

use App\Filament\ChangYang\Resources\PersonCategoryResource\Pages;
use App\Models\ChangYang\PersonCategory;
use App\Filament\ChangYang\Support\ChangYangPageLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonCategoryResource extends Resource
{
    protected static ?string $model = PersonCategory::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function listUrl(): string
    {
        return ChangYangPageLink::tab('people');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('顯示標題')->required()->maxLength(255),
            Forms\Components\Toggle::make('is_active')->label('顯示於人物頁')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('顯示標題')->searchable(),
                Tables\Columns\TextColumn::make('roles_count')->counts('roles')->label('人物角色數'),
                Tables\Columns\IconColumn::make('is_active')->label('顯示')->boolean(),
            ])
            ->reorderable('sort_order')
            ->actions([Tables\Actions\EditAction::make()->label('編輯')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonCategories::route('/'),
            'edit' => Pages\EditPersonCategory::route('/{record}/edit'),
        ];
    }
}
