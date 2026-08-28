<?php

namespace App\Services\PlantCatalog;

use App\Models\FsSeedsSplist;
use App\Models\PlantCatalog\SiteSpecies;
use Illuminate\Support\Collection;

class FushanSeedSpeciesService
{
    /**
     * Seed-rain settings remain in fs_seeds.splist. For identified species,
     * site csp/spcode and all taxonomy are supplied by plant_catalog.
     */
    public function rows(): Collection
    {
        $catalog = SiteSpecies::query()
            ->fushan()
            ->withChecklistTaxonomy()
            ->get()
            ->keyBy('spcode');

        return FsSeedsSplist::query()->get()->map(function ($setting) use ($catalog) {
            $row = $setting->toArray();
            $species = $catalog->get(trim((string) $setting->sp));

            if ($species !== null) {
                $row['csp'] = $species->csp;
                $row['sp'] = $species->spcode;
                $row['canonical_name'] = $species->now_simname;
                $row['full_name'] = $species->full_name;
                $row['family'] = $species->apgfamily;
                $row['chfamily'] = $species->chapgfamily;
                $row['genus'] = $species->genus;
                $row['growth_form'] = $species->life_form;
            }

            return $row;
        });
    }

    public function keyedByCsp(): array
    {
        return $this->rows()->keyBy('csp')->all();
    }

    public function cspList(): array
    {
        return $this->rows()->pluck('csp')->filter()->values()->all();
    }
}
