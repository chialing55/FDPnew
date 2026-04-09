<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;

class MortalityDataviewer extends Component
{
    public $user;
    public $site;

    public function render()
    {
        return view('livewire.fushan.mortality-dataviewer');
    }
}
