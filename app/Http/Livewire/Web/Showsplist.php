<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Facades\Schema;

use App\Models\PlantCatalog\SiteSpecies;
use App\Http\Controllers\UpdateController;

//網頁物種清單
class Showsplist extends Component
{
    public $user;
    public $splist;

    public function mount()
    {
        $request = request();

        $latestUpdate = $request->session()->get('latest_update', function () {
            return 'no';
        });

        if ($latestUpdate === 'no') {
            $ob_update = new UpdateController;
            $latestUpdate = $ob_update->latestUpdates();
          
            $request->session()->put('latest_update', $latestUpdate);
        }


        $researchLinks = $this->speciesResearchLinks();

        $this->splist = SiteSpecies::query()
            ->fushan()
            ->withChecklistTaxonomy()
            ->orderBy('checklist.family')
            ->orderBy('checklist.canonical_name')
            ->get()
            ->map(function ($species) use ($researchLinks) {
                $row = $species->toArray();
                $row['researches'] = $researchLinks[$species->spcode] ?? [];
                return $row;
            })
            ->toArray();
    }

    private function speciesResearchLinks(): array
    {
        if (!Schema::connection('plant_catalog')->hasTable('species_research_links')) {
            return [];
        }

        return collect(\DB::connection('plant_catalog')->table('species_research_links')
            ->where('site', 'fushan')->select('spcode', 'research_code')->get())
            ->groupBy('spcode')
            ->map(function ($links) {
                return $links->pluck('research_code')
                    ->mapWithKeys(fn ($researchCode) => [$researchCode => 1])
                    ->all();
            })
            ->all();
    }

    public function render()
    {
        return view('livewire.web.showsplist');
    }
}
