<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeEntrycom;

class TreeCompare extends Component
{

    public $comnote;
    public $entrylist=[];
    public $comparelist=[];
    public $qx;

    public function mount(){
// 確認是否輸入完資料
        $comnote='';
        $entrylist=[];
        $comparelist=[];

        $entrycom=FsTreeEntrycom::select('qx',  DB::raw('SUM(entry1) as sum1'), DB::raw('SUM(entry2) as sum2'))->groupBy('qx')->get()->toArray();
        $compareok=FsTreeEntrycom::select('qx',  DB::raw('SUM(compareOK) as sum1'))->groupBy('qx')->get()->toArray();

        foreach ($entrycom as $entry){
            if ($entry['sum1']==25 && $entry['sum2']==25){
                $entrylist[]=$entry['qx'];
            }
        }


        foreach ($compareok as $entry){
            if ($entry['sum1']==25){
                $comparelist[]=$entry['qx'];
            }
        }

        $this->entrylist=$entrylist;
        $this->comparelist=$comparelist;

    }


    public function render()
    {
        return view('livewire.fushan.tree-compare');
    }
}
