<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use App\Models\Web\Page;

class PageDefault extends Component
{

    public string $slug;
    public $page;

    public function mount($slug)
    {
        $this->page = Page::where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        return view('livewire.web.page-default');
    }
}
