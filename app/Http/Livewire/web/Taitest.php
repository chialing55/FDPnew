<?php

namespace App\Http\Livewire\Web;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Taitest extends Component
{

    public $user;
    public $folder1List=['TAIimage', 'Typeimage'];
    public $folder2List=[];
    public $folder1;

    public function mount(){

    }

    public function searchfolder2(Request $request, $folder1){

        // dd($folder1);
        $this->folder2List=['test'];

    }

    public function render()
    {
        return view('livewire.web.taitest');
    }
}
