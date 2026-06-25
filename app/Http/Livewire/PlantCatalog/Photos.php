<?php

namespace App\Http\Livewire\PlantCatalog;

use App\Models\FsBaseSpinfo;
use App\Models\Web\Photo;
use Livewire\Component;

class Photos extends Component
{
    public array $splist = [];

    public function mount(): void
    {
        $photoCounts = Photo::query()
            ->selectRaw('spcode, count(*) as photo_count')
            ->groupBy('spcode')
            ->pluck('photo_count', 'spcode')
            ->toArray();

        $this->splist = FsBaseSpinfo::query()
            ->orderBy('apgfamily')
            ->orderBy('now_simname')
            ->get()
            ->map(function ($species) use ($photoCounts) {
                $row = $species->toArray();
                $row['photo_count'] = (int) ($photoCounts[$species->spcode] ?? 0);

                return $row;
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.plant-catalog.photos');
    }
}
