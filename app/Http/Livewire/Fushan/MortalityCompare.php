<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;

class MortalityCompare extends Component
{
    public $statusNote = '死亡率調查資料比對頁面已建立，後續可在此補上第一次與第二次輸入的比對規則。';

    public function render()
    {
        return view('livewire.fushan.mortality-compare');
    }
}
