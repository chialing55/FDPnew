<?php

namespace App\Filament\Resources\SiteResource\RelationManagers;

use App\Models\Web\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiteTeamsRelationManager extends RelationManager
{
    /** 對應 Site model 上的關係名稱：siteTeams() */
    protected static string $relationship = 'siteTeams';

    protected static ?string $title = '研究團隊';

    public static function getModelLabel(): string
    {
        return '團隊關係';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('team_id')
                ->label('團隊')
                // SiteTeam 上的關係名稱 team()
                ->relationship('team')
                // 每一列顯示：機構 / 部門 / PI
                ->getOptionLabelFromRecordUsing(function (Team $team) {
                    return $team->display_name;
                })
                ->searchable()
                // 搜尋時同時比對三個欄位
                ->getSearchResultsUsing(function ($query, string $search) {
                    return $query
                        ->where('institution_zh_tw', 'like', "%{$search}%")
                        ->orWhere('department_zh_tw', 'like', "%{$search}%")
                        ->orWhere('pi_name_zh_tw', 'like', "%{$search}%")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn (Team $team) => [
                            $team->id => $team->display_name,
                        ]);
                })
                ->preload()
                ->required()
                ->native(false),

                Forms\Components\Select::make('role')
                    ->label('角色')
                    ->options([
                        'plot_manager' => '樣區負責人',
                        'team_partner' => '合作單位',
                    ])
                    ->placeholder('請選擇角色')
                    ->searchable()   // 如需可搜尋
                    ->required()     // 如需必填
                    ->columnSpan(2),

                Forms\Components\TextInput::make('sort_order')
                    ->label('排序')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(1),
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('role')
            ->columns([
                Tables\Columns\TextColumn::make('team.display_name')
                    ->label('團隊')
                    ->sortable(
                        query: function (Builder $query, string $direction): Builder {
                            return $query
                                ->join('teams', 'site_team.team_id', '=', 'teams.id')
                                ->orderBy('teams.institution_zh_tw', $direction)
                                ->select('site_team.*');
                        }
                    )
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('team', function (Builder $q) use ($search) {
                                $q->where('institution_zh_tw', 'like', "%{$search}%")
                                ->orWhere('department_zh_tw', 'like', "%{$search}%")
                                ->orWhere('pi_name_zh_tw', 'like', "%{$search}%");
                            });
                        }
                    ),
                Tables\Columns\TextColumn::make('role')
                    ->label('角色')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('排序')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('新增團隊'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('編輯'),
                Tables\Actions\DeleteAction::make()->label('刪除'),
            ])
            ->defaultSort('sort_order');
    }
}
