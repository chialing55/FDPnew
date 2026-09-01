<?php

namespace App\Models\PlantCatalog;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSpecies extends PlantCatalogModel
{
    protected $table = 'site_species';

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(TaiwanChecklist::class, 'code', 'spcode');
    }

    public function scopeForSite(Builder $query, string $site): Builder
    {
        return $query->where($this->qualifyColumn('site'), $site);
    }

    public function scopeFushan(Builder $query): Builder
    {
        return $query->forSite('fushan');
    }

    public function scopeNanjenshan(Builder $query): Builder
    {
        return $query->forSite('nanjenshan');
    }

    /**
     * Join checklist taxonomy while retaining the field names expected by the
     * existing Fushan views. Taxonomic values must come from taiwan_checklist.
     */
    public function scopeWithChecklistTaxonomy(Builder $query): Builder
    {
        return $query
            ->leftJoin('taiwan_checklist as checklist', 'checklist.spcode', '=', 'site_species.code')
            ->select([
                'site_species.*',
                'checklist.canonical_name as now_simname',
                'checklist.canonical_name as spcode_simname',
                'checklist.chname as checklist_chname',
                'checklist.full_name',
                'checklist.genus',
                'checklist.family as apgfamily',
                'checklist.chfamily as chapgfamily',
                'checklist.growth_form as life_form',
            ]);
    }
}
