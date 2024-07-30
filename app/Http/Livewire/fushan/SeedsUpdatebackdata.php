<?php

namespace App\Http\Livewire\Fushan;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;

use App\Jobs\SeedsAddButton;


//檢視修改種子大表
class SeedsUpdatebackdata extends Component
{

    public $selectCensus='';
    public $censuslist=[];
    // public $censusdata=[];

    public function mount(){

        $census=FsSeedsFulldata::select('census')->groupBy('census')->orderByDesc('census')->get()->toArray();
        // dd($census);
        foreach ($census as $item) {
            $censuslist[] = $item['census'];
        }

        $this->censuslist=$censuslist;
        // dd($censuslist);


    }

    public function searchCensus(){
        $census=$this->selectCensus;
        
        $this->createTable($census);

    }

    public $censusdata=[];
    public $identifier='蔡佳秀';  //預設鑑定者

//建立檢視表單
    public function createTable($census){

        // $entrytable1=FsSeedsRecord1::query()->orderBy('trap', 'asc')->orderBy('csp', 'asc')->orderBy('code', 'asc')->get()->toArray();

        $censusdata1=FsSeedsFulldata::where('census', 'like', $census)->get()->toArray();

        $ob_table = new SeedsAddButton;
        $censusdata=$this->censusdata=$ob_table->addbutton($censusdata1, 'fulldata');


        $fsscsplist1 = FsSeedsFulldata::select('csp', DB::raw('count(trap) as count2'))->where('csp', 'not like', 'nothing')->groupBy('csp')->orderByDesc('count2')->get()->toArray();
        $fsscsplist2 = FsSeedsSplist::select('csp')->get()->toArray();

        for($i=0;$i<count($fsscsplist1);$i++){

            $fsscsplist[]=$fsscsplist1[$i]['csp'];
        }

        for($i=0;$i<count($fsscsplist2);$i++){

            if (!in_array($fsscsplist2[$i]['csp'], $fsscsplist)){
                $fsscsplist[]=$fsscsplist2[$i]['csp'];
            }

            
        }

        for($k=0;$k<29;$k++){
            $emptytable[$k]['id']=$k+1;
            $emptytable[$k]['census']=$census;
            $emptytable[$k]['trap']='';
            $emptytable[$k]['csp']='';
            $emptytable[$k]['code']='';
            $emptytable[$k]['count']='';
            $emptytable[$k]['seeds']='';
            $emptytable[$k]['viability']='';
            $emptytable[$k]['fragments']='';
            $emptytable[$k]['sex']='';
            $emptytable[$k]['identifier']=$this->identifier;
            $emptytable[$k]['note']='';
        }

        // dd($inlist);
        // $this->entry='y';
        // $this->thiscensus=$census;

        $this->dispatchBrowserEvent('data', [ 'census' => $census, 'record' => $censusdata, 'emptytable' => $emptytable, 'csplist' => $fsscsplist]);

    }
    public function render()
    {
        return view('livewire.fushan.seeds-updatebackdata');
    }
}
