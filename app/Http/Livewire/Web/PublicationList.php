<?php

namespace App\Http\Livewire\Web;

use App\Models\Web\Publication;
use Livewire\Component;
use Livewire\WithPagination;

class PublicationList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.web.publication-list', [
            'publications' => Publication::latestFirst()->paginate(30),
        ]);
    }
}
