<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Redirect;


class TreeDataviewer extends Component
{

    public $qx='0';
    public $qy='0';
    public $census='1';
    public $oldnew='old';
    public $error='';
    public $path='';
    public $filePath2='';

    public function mount(Request $request)
    {
        $this->processFile($request);

    }

    public function change(Request $request)
    {
        $this->processFile($request);
    }


    public function processFile(Request $request)
    {

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        $fileqx=str_pad($this->qx, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy, 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;
        $filecensus='fs_census'.$this->census."_scanfile";


///fs_census4_scanfile/'.$fileqx.'/old/'.$filesqx.'_old.pdf
        $filePath1=$filecensus."/".$fileqx.'/'.$this->oldnew.'/'.$filesqx.'_'.$this->oldnew.'.pdf';
        $filePath2=public_path($filePath1);
        $this->filePath2=$filePath2;

        if (file_exists($filePath2)) {
            // return Redirect::away($filePath1);
            $this->path=$filePath1;
            $this->error='';
        } else {
            $this->error='沒有檔案 '.$filePath1;
            $this->path='';
        }

    }

    public function render()
    {
        return view('livewire.fushan.tree-dataviewer');
    }
}
