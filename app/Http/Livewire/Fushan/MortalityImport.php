<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;

class MortalityImport extends Component
{
    public $user;
    public $site;
    public $importNote = '死亡率調查匯入流程頁面已建立，後續可在此串接正式匯入邏輯。';

    public function render()
    {
        return view('livewire.fushan.mortality-import');
    }
}
