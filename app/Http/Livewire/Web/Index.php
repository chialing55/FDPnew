<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class Index extends Component
{

    public function render()
    {
        return view('livewire.web.index');
    }
}
