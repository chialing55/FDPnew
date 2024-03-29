<?php

namespace App\Http\Livewire\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;


use App\Models\Ss1haData2015;
use App\Models\Ss1haRecord1;
use App\Models\Ss1haRecord2;
use App\Models\Ss1haEnviR1;
use App\Models\Ss1haEnviR2;

use App\Models\SsSplist;

use App\Jobs\FsTreeAddButton;

class S1haShowentry extends Component
{
    public $entry;
    public $user;
    public $site;
    


    public $selectPlot;
    public $record;
    public $entrynote;
    public $qx='';
    public $qy='';
    public $sqx;
    public $sqy;
    public $cov;

    public function mount(){

        if ($this->entry == '1') {
            $table= new Ss1haRecord1;
            $tableenvi= new Ss1haEnviR1;
            $entryother='2';
        } else {
            $table= new Ss1haRecord2;
            $tableenvi= new Ss1haEnviR2;
            $entryother='1';
        }

        $data=$table::where('date', 'like', '0000-00-00')->get()->toArray();

        if (count($data)==0){
            $this->entrynote='第'.$this->entry.'次輸入已完成。 若以完成第'.$entryother.'次輸入，可進行資料比對。';
        }

    }

    public function searchSite(Request $request, $qx, $qy, $sqx, $sqy){

        

        if ($this->entry == '1') {
            $table= new Ss1haRecord1;
            $tableenvi= new Ss1haEnviR1;
            $entryother='2';
        } else {
            $table= new Ss1haRecord2;
            $tableenvi= new Ss1haEnviR2;
            $entryother='1';
        }

        // dd($qx, $qy);
        // 新增資料輸入種類用

        $ss1hacsplist=$request->session()->get('ss1hacsplist', function () {
            return 'no';
        });


        if ($ss1hacsplist=='no'){
            $ss1hacsplist=[];
           

            $csplist1 = $table::select('csp', DB::raw('count(stemid) as count2'))->groupBy('csp')->orderByDesc('count2')->get()->toArray();

            $splist=SsSplist::select('index')->orderBy('index', 'asc')->get()->toArray();

            foreach ($csplist1 as $list) {
                $csplist2[]=$list['csp'];
            }

            foreach ($splist as $list){
                if (!in_array($list['index'], $csplist2)){
                    $csplist2[]=$list['index'];
                }
            }
        
            $ss1hacsplist=$csplist2;
            $request->session()->put('ss1hacsplist', $ss1hacsplist);
        } 
        

          
        

// dd($ss1hacsplist);


        $envi=$tableenvi::query()->where('qx', 'like', $qx)->where('qy', 'like', $qy)->get()->toArray();
        $records=$table::query()->where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get()->toArray();


        //新增樹為刪除按鍵，其他加入特殊修改按鍵
        if (count($records)>0){

            $ob_redata = new FsTreeAddButton;
            $result=$ob_redata->addbutton($records, $this->entry);
        } else {
            $result='無';
        }



        $this->record=$result;
        $this->datasavenote='';
        $this->qx=$qx;
        $this->qy=$qy;
        $this->sqx=$sqx;
        $this->sqy=$sqy;


        //recruittable
        for($k=0;$k<30;$k++){
            $emptytable[$k]['qx']=$qx;
            $emptytable[$k]['qy']=$qy;
            $emptytable[$k]['sqx']='';
            $emptytable[$k]['sqy']='';
            $emptytable[$k]['tag']='';
            $emptytable[$k]['branch']='0';
            $emptytable[$k]['csp']='';
            $emptytable[$k]['dbh']='';
            $emptytable[$k]['note']='';
            $emptytable[$k]['code']='';
            $emptytable[$k]['ill']='';
            $emptytable[$k]['leave']='';
            $emptytable[$k]['pom']='1.3';
            $emptytable[$k]['date']='';
            $emptytable[$k]['tofix']='';
        }

        $this->dispatchBrowserEvent('data', ['record' => $result, 'emptytable' => $emptytable, 'csplist' => $ss1hacsplist,  'envi' => $envi]);


    }

    public function submitForm(Request $request){

        $this->searchSite($request, $this->qx, $this->qy, 1, 1);
    }


    public function render()
    {
        return view('livewire.shoushan.s1ha-showentry');
    }
}
