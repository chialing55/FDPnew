<?php

namespace App\Http\Livewire\Nanjenshan;

use Livewire\Component;

class SeedlingDataviewer extends Component
{
    public $user;
    public $site;

    public function render()
    {
        return view('livewire.nanjenshan.seedling-dataviewer');
    }
}
