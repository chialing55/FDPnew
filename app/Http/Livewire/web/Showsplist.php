<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Response;
use Livewire\WithPagination;

use App\Models\FsBaseSpinfo;
use App\Http\Controllers\UpdateController;

//網頁物種清單
class Showsplist extends Component
{
    public $user;
    public $splist;

    public function mount(Request $request){

        $lasterUpdate=$request->session()->get('lasterUpdate', function () {
            return 'no';

        });

        if ($lasterUpdate=='no'){
            $lasterUpdate='';
            $ob_update = new UpdateController;
            $lasterUpdate=$ob_update->latestUpdates();
          
            $request->session()->put('latest_update', $lasterUpdate);
        }


        $this->splist = FsBaseSpinfo::select('fs_base.spinfo.*', DB::raw('(EXISTS (SELECT 1 FROM fs_web.photo WHERE fs_base.spinfo.spcode = fs_web.photo.spcode)) as has_photo'))->get()->toArray();

        // dd($this->splist);


    }


    public function render()
    {
        return view('livewire.web.showsplist');
    }
}
