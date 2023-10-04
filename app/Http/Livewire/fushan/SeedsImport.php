<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;

class SeedsImport extends Component
{

    public $user;
    public $date;
    public $census;

    public function mount(){
        $this->census=FsSeedsDateinfo::query()->max('census');
    }


    public function render()
    {
        return view('livewire.fushan.seeds-import');
    }
}
