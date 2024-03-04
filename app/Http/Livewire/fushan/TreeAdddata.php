<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;


use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeEntrycom;
use App\Models\FsTreeCensus5;

use App\Jobs\FsTreeCensus5Progress;

class TreeAdddata extends Component
{

//新增資料至census5及base

    public $tableVar;
    public $show;
    public $comparelist=[];
    public $updatelist=[];
    public $qx;
    public $user;
    public $directories = [];

    public function mount(Request $request){

        $ob_result = new FsTreeCensus5Progress;
        $result=$ob_result->showProgress();

        // Extract only the directory names
        $this->directories = $result['directorieslist']; //上傳檔案
        // dd($this->directories);
        $this->updatelist=$result['updatelist'];  //已匯入大表
        $this->comparelist=$result['comparelist'];  //已比對完成
    }


    public function addData(Request $request){
        $splist = $request->session()->get('splist');
        $csplist=$request->session()->get('csplist', function () {
            return 'no';
        });
        // dd($qx, $qy);
        // 新增資料輸入種類用
        if ($csplist=='no'){
            $csplist=[];

            $csplist1 = FsTreeRecord1::select('spcode', DB::raw('count(stemid) as count2'))->groupBy('spcode')->orderByDesc('count2')->get()->toArray();


            for($i=0;$i<count($csplist1);$i++){
                $csplist[$i]=$splist[$csplist1[$i]['spcode']];
            }

            foreach ($splist as $key=>$value){
                if (!in_array($value, $csplist)){
                    $csplist[]=$value;
                }
            }

        
            $request->session()->put('csplist', $csplist);
        }
// dd($csplist);

        for($k=0;$k<5;$k++){
            $emptytable[$k]['qx']='';
            $emptytable[$k]['qy']='';
            $emptytable[$k]['branch']='0';
            $emptytable[$k]['pom']='1.3';
            $emptytable[$k]['date']='';
            $emptytable[$k]['code']='';
            $emptytable[$k]['sqx']='';
            $emptytable[$k]['sqy']='';
            $emptytable[$k]['tag']='';
            $emptytable[$k]['csp']='';
            $emptytable[$k]['dbh']='';
            $emptytable[$k]['note']='';
            $emptytable[$k]['tofix']='';

        }

        $this->dispatchBrowserEvent('updata', [ 'emptytable' => $emptytable, 'csplist' => $csplist, 'updatelist' => $this->updatelist]);
        $this->show='ok';
    }

    public function render(Request $request)
    {

        return view('livewire.fushan.tree-adddata');
    }
}
