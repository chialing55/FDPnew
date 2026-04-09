<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;

class MortalityShowentry extends Component
{
    public $entry;
    public $user;
    public $site;

    public function render()
    {
        return view('livewire.fushan.mortality-showentry');
    }
}
