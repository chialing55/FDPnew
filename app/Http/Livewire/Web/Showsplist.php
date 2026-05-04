<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Facades\Schema;

use App\Models\FsBaseSpinfo;
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

        $this->splist = FsBaseSpinfo::query()
            ->orderBy('apgfamily')
            ->orderBy('now_simname')
            ->get()
            ->map(function ($species) use ($researchLinks) {
                $row = $species->toArray();
                $row['researches'] = $researchLinks[$species->spcode] ?? $this->legacyResearchFlags($row);
                return $row;
            })
            ->toArray();
    }

    private function speciesResearchLinks(): array
    {
        if (!Schema::connection('mysql4')->hasTable('species_research_links')) {
            return [];
        }

        return collect(\DB::connection('mysql4')->table('species_research_links')->select('spcode', 'research_code')->get())
            ->groupBy('spcode')
            ->map(function ($links) {
                return $links->pluck('research_code')
                    ->mapWithKeys(fn ($researchCode) => [$researchCode => 1])
                    ->all();
            })
            ->all();
    }

    private function legacyResearchFlags(array $species): array
    {
        return [
            'tree' => (int) ($species['tree'] ?? 0),
            'seed' => (int) ($species['seed'] ?? 0),
            'seedling' => (int) ($species['seedling'] ?? 0),
            'mortality' => 0,
        ];
    }


    public function render()
    {
        return view('livewire.web.showsplist');
    }
}
