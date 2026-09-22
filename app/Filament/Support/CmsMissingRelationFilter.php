<?php

namespace App\Filament\Support;

use App\Models\Web\Project;
use App\Models\Web\Publication;
use Illuminate\Database\Eloquent\Builder;

class CmsMissingRelationFilter
{
    public static function query(string $type): Builder
    {
        $model = $type === 'publications' ? Publication::class : Project::class;

        return $model::query()->where(function (Builder $query) use ($type): void {
            $query->whereDoesntHave('subjects');

            if ($type === 'publications') {
                // 「與所有樣區無關」是已確認的結果，不列為待補樣區關聯。
                $query->orWhere(function (Builder $siteQuery): void {
                    $siteQuery->whereDoesntHave('sites')
                        ->where(function (Builder $statusQuery): void {
                            $statusQuery->whereNull('site_review_status')
                                ->orWhere('site_review_status', '!=', 'not_related');
                        });
                });

                return;
            }

            $query->orWhereDoesntHave('sites');
        });
    }
}
