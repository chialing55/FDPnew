<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use App\Models\Web\Page;

class Index extends Component
{
    public $plots=['fushan', 'nanjenshan', 'shoushan'];
    public $indexIntro;

    public function mount(): void
    {
        $indexIntro = Page::where('slug', 'index-intro')->first();
        $this->indexIntro = $indexIntro;
    }

    public function render()
    {
        return view('livewire.web.index');
    }
}
