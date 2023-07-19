<?php

namespace App\Http\Livewire\Fushan;


use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsTreeProgress;
use App\Models\FsTreeRecord1;

class TreeShowprogress extends Component
{


    
    public $date;
    public $new=0;
    public $qx=0;
    public $qy=0;
    public $ind=0;
    public $period=0;
    public $sqx=[];
    public $table=[];
    public $note1;
    public $plots=[];
    public $person1;
    public $person2;
    public $person3;
    public $person4;


    public $date2;
    public $qx2;
    public $note2;
    public $finishSite=[];
    public $countFinishSite=0;


    public function mount(){

            $this->date = date('Y-m-d');
            
            $table1 = FsTreeProgress::orderByDesc('date')->get()->toArray();
            if (!empty($table1)) {
                $this->table = $table1;
                // $this->finishSite=$table1;
                $this->finishSiteTable();
                $this->qxqychange();
                
            }
            

        // dd($this);
    }




    public function finishSiteTable()  
    {
        // dd($this->finishSite);    
        $countFinishSite=0; 
            $table2s = FsTreeProgress::select('qx', 'qy', DB::raw('SUM(plot_num) as plots'))->groupBy('qx', 'qy')->orderBy('qx', 'ASC')->get()->toArray();
            $finishSite2 = [];

            foreach ($table2s as $table2) {
                $finishSite2["'".$table2['qx'].$table2['qy']."'"] = $table2['plots'];
                
                    $countFinishSite=$countFinishSite+$table2['plots'];

                
            }

            $this->finishSite = $finishSite2;
            $this->countFinishSite= $countFinishSite/16;
            // return($finishSite2);
        
    }

    protected $listeners = ['updateValue' => 'updateValue'];

    public function updateValue($value){
        $uniqueArray = array_unique($value, SORT_REGULAR);  //去除重覆值
        $filteredArray = array_values(array_filter($uniqueArray));  //去除空索引
        $this->sqx=$filteredArray;

    }

    public function submitForm(Request $request)
    {
        $user = $request->session()->get('user', function () {
            return 'no';
        });

        $inlist=['date'=>$this->date, 'id'=>'0', 'qx' => $this->qx, 'qy'=>$this->qy, 'new_branch'=>$this->new, 'person' => $this->ind, 'plot_num'=>count($this->sqx), 'period' => $this->period, 'update_id' => $user, 'updated_at' => date("Y-m-d H:i:s")];
        $orib=0;
        $plots='';
        $i=0;
        foreach ($this->sqx as $plot){
            $s_orib=FsTreeRecord1::where('qx', 'like', $this->qx)->where('qy', 'like', $this->qy)->where('sqx', 'like', $plot[0])->where('sqy', 'like', $plot[1])->where('show', 'like', '1')->where('status', 'not like', '-9')->get()->toArray();
            $orib=$orib+count($s_orib);
            $i++;
            // $plots=$plots."[{$plot[0]}, {$plot[1]}]";
            // if ($i%4==0){
            //     $plots=$plots."<br>";
            // }

        }
        $qx=$this->qx;
        $qy=$this->qy;

        //調查人員
        $person='';
        $persons = [$this->person1, $this->person2, $this->person3, $this->person4];
        $persons = array_filter($persons); // 移除空值
        $person = implode(", ", $persons);

        $plots = json_encode($this->sqx);

        $inlist['ori_branch']=$orib;
        $inlist['plots']=$plots;
        $inlist['personslist']=$person;

        
        FsTreeProgress::insert($inlist);

        $this->reset();
        $this->note1='已儲存資料';
        $this->qx=$qx;
        $this->qy=$qy;
        $this->mount();
        // $this->finishSiteTable();
        // dd($this);    
        // $this->finishSiteTable();
// dd($this);
        $this->dispatchBrowserEvent('reProgress', ['plots'=>$this->plots]);

    }

    public function deleteForm(Request $request){

        if (!is_null($this->date2) && !is_null($this->qx2)){
        FsTreeProgress::where('date', 'like', $this->date2)->where('qx', 'like', $this->qx2)->delete();
        }
        // dd($this);
        $this->reset();
        $this->note2='已刪除資料';
        $this->mount();
        // $this->finishSiteTable();
        $this->dispatchBrowserEvent('reProgress', ['plots'=>$this->plots]);
    }


//換右邊選小區的表格
    public function qxqychange(){
        $array=[];
        $tables = FsTreeProgress::where('qx', 'like', $this->qx)->where('qy', 'like', $this->qy)->get()->toArray();
        if (!empty($tables)){
        foreach ($tables as $table){
            // dd($table['plots']);
            $array1=json_decode($table['plots'], true);
            $array=array_merge($array, $array1);
            }
        }

        $this->plots=$array;
        $this->dispatchBrowserEvent('rePlots', ['plots'=>$array]);
// dd($array);
    }

    public function render()
    {
        // dd($this);
        return view('livewire.fushan.tree-showprogress');
    }
}
