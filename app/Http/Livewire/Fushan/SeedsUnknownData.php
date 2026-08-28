<?php

namespace App\Http\Livewire\Fushan;

use App\Jobs\SeedsAddButton;
use App\Models\FsSeedsFulldata;
use App\Services\PlantCatalog\FushanSeedSpeciesService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SeedsUnknownData extends Component
{
    public string $unk = '';
    public ?string $user = null;
    public ?string $site = null;
    public array $censusdata = [];
    public string $identifier = '黃小俊';
    private string $tableKey = 'unknown';

    public function mount(string $unk, ?string $user = null, ?string $site = null): void
    {
        abort_unless((bool) auth()->user()?->is_admin, 403);

        $this->unk = $unk;
        $this->user = $user;
        $this->site = $site;
    }

    public function loadUnknownData(): void
    {
        $this->censusdata = (new SeedsAddButton)->addbutton($this->unknownRows(), 'fulldata');

        $this->dispatch(
            'data',
            census: $this->tableKey,
            record: $this->censusdata,
            emptytable: $this->emptyTable(),
            csplist: $this->speciesList()
        );
    }

    public function render()
    {
        return view('livewire.fushan.seeds-unknown-data');
    }

    private function speciesList(): array
    {
        $usedSpecies = FsSeedsFulldata::query()
            ->select('csp', DB::raw('count(trap) as count2'))
            ->where('csp', 'not like', 'nothing')
            ->groupBy('csp')
            ->orderByDesc('count2')
            ->pluck('csp')
            ->toArray();

        $listedSpecies = (new FushanSeedSpeciesService)->cspList();

        return array_values(array_unique(array_merge([$this->unk], $usedSpecies, $listedSpecies)));
    }

    private function unknownRows(): array
    {
        return FsSeedsFulldata::query()
            ->where(function ($query) {
                $query->where('csp', $this->unk)
                    ->orWhere('sp', $this->unk);
            })
            ->orderBy('census')
            ->orderBy('trap')
            ->orderBy('code')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    private function emptyTable(): array
    {
        $emptytable = [];

        for ($k = 0; $k < 29; $k++) {
            $emptytable[] = [
                'id' => $k + 1,
                'census' => '',
                'trap' => '',
                'csp' => $this->unk,
                'code' => '',
                'count' => '',
                'seeds' => '',
                'viability' => '',
                'fragments' => '',
                'sex' => '',
                'identifier' => $this->identifier,
                'note' => '',
            ];
        }

        return $emptytable;
    }
}
