<?php

namespace App\Http\Livewire\PlantCatalog;

use App\Models\PlantCatalog\SiteSpecies;
use App\Models\Web\Photo;
use Livewire\Component;

class Photos extends Component
{
    public array $splist = [];

    public function mount(): void
    {
        $photoCounts = Photo::query()
            ->selectRaw('code, count(*) as photo_count')
            ->whereNotNull('code')
            ->groupBy('code')
            ->pluck('photo_count', 'code')
            ->toArray();

        $this->splist = SiteSpecies::query()
            ->fushan()
            ->withChecklistTaxonomy()
            ->orderBy('checklist.family')
            ->orderBy('checklist.canonical_name')
            ->get()
            ->map(function ($species) use ($photoCounts) {
                $row = $species->toArray();
                $row['photo_count'] = (int) ($photoCounts[$species->code] ?? 0);

                return $row;
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.plant-catalog.photos');
    }
}
