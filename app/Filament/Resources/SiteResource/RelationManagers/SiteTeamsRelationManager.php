<?php

namespace App\Filament\Resources\SiteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SiteTeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'siteTeams';

    protected static ?string $title = '參與團隊';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(6)->schema([
                    Forms\Components\Select::make('team_id')
                        ->label('團隊')
                        ->relationship('team', 'institution_zh_tw')   // 顯示機構名稱（中）
                        ->searchable()
                        ->required()
                        ->native(false)
                        ->helperText('選擇參與此樣區的團隊')
                        ->columnSpan(3),

                    Forms\Components\TextInput::make('role')
                        ->label('角色說明')
                        ->maxLength(255)
                        ->helperText("例如：'PI'、'co-PI'、'partner'")
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('排序')
                        ->numeric()
                        ->default(0)
                        ->columnSpan(1),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('team.institution_zh_tw')
                    ->label('機構')
                    ->searchable(),

                Tables\Columns\TextColumn::make('team.department_zh_tw')
                    ->label('系所 / 單位')
                    ->limit(20),

                Tables\Columns\TextColumn::make('team.pi_name_zh_tw')
                    ->label('負責人')
                    ->limit(20),

                Tables\Columns\TextColumn::make('role')
                    ->label('角色'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('新增團隊'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
