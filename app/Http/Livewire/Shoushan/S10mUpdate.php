<?php

namespace App\Http\Livewire\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;


use App\Models\Ss10mQuad2014;
use App\Models\Ss10mTree2014;
use App\Models\Ss10mTree2015;
use App\Models\Ss10mTreeData2024;
use App\Models\Ss10mTreeBase2024;
use App\Models\Ss10mTreeBaseR2024;

use App\Models\Ss10mTreeRecord1;

use App\Models\SsSplist;


use App\Jobs\TreeCompareCheck;
use App\Jobs\SsPlotFinishCheck;


class S10mUpdate extends Component
{

    public $csplist=[];
    public $splist=[];
    public $user;
    public $addclass1='';
    public $addclass2='';
    public $alternotedown='';
    public $from;
    public $plot;


    public $stemid;
    public $tag='';
    public $branch='';

    public $stemidlist=[];
    public $go;
    public $goto;
    public $type;

    public $finishnote;

    public function mount(Request $request){


        $this->fcsplist($request);
// dd($this->splist);

    }

    public function alternote(Request $request){
        // $plot=$this->plot;
        $this->stemidlist=[];

        $stemidlist=Ss10mTreeData2024::where('10m_tree_data_2024.alternote', '!=', '')->join('10m_tree_base_2024', '10m_tree_data_2024.tagid', '=', '10m_tree_base_2024.tagid')->orderBy('10m_tree_data_2024.stemid')->pluck('10m_tree_data_2024.stemid')->toArray();

        // dd($stemidlist);
        if (count($stemidlist)>0){
            $this->stemidlist=$stemidlist;
            $this->searchStemid(0);
        }
        $this->go='yes';
        $this->type='1';
        // dd('1');

    }

    protected $listeners = ['updateStemidlist' => 'updateStemidlist'];

    public function updateStemidlist($data){
        $thisstemid = $data['thisstemid'];
        $from = $data['from'];
        // $qx=$this->qx;

        if ($from=='alternote'){
            $stemidlist=Ss10mTreeData2024::where('10m_tree_data_2024.alternote', '!=', '')->join('10m_tree_base_2024', '10m_tree_data_2024.tagid', '=', '10m_tree_base_2024.tagid')->orderBy('10m_tree_data_2024.stemid')->pluck('10m_tree_data_2024.stemid')->toArray();
            if (count($stemidlist)>0){
                $this->stemidlist=$stemidlist;
                $stemidkey = array_search($thisstemid, $stemidlist);
                if ($stemidkey != false){
                    $this->searchStemid($stemidkey);
                } else {
                    $this->searchStemid(0);
                }
                
            }
        } else {
            $this->indStemid();
        }
        // dd($stemidlist);
    }

//從特殊修改來的
    public function searchStemid($key){
        // dd($stemid); 
        $this->tag='';
        $this->branch='';
        $this->finishnote='';
        $this->goto='';
        $this->from='alternote';

        if (isset($this->stemidlist[$key]))
        {
        $stemid=$this->stemidlist[$key];} else {
            $stemid=$this->stemidlist[0];
            $this->goto='1';
        }
            $stemiddata=$this->nowStemidData($stemid);
            $this->dispatchBrowserEvent('stemiddata', ['stemid'=>$this->stemid,'stemdata' => $stemiddata,  'csplist' => $this->csplist, 'from' => 'alternote']);
    }

    public function indStemid(Request $request){
        $plot=$this->plot;
        $tag=$this->tag;
        $b=$this->branch;
        $tagid=$plot.'-'.$tag;
        $this->stemidlist=[];
        // $this->plot='';
        $this->from='indStemid';
        // dd($tag);

        if ($tagid!=''){

            $this->go='yes';
            $this->type='2';
            if ($b==''){$b='0';}
            $stemid=$tagid.".".$b;
            //確認是否以匯入大表
            $census5 = Ss10mTreeData2024::where('stemid', 'like', $stemid)->get()->toArray();
            if (count($census5)>0){
            $this->stemidlist[0]=$stemid;
            $stemiddata=$this->nowStemidData($stemid);

            $this->dispatchBrowserEvent('stemiddata', ['stemid'=>$this->stemid,'stemdata' => $stemiddata,  'csplist' => $this->csplist, 'from' => 'indStemid']);
            } else {
                $this->go='no';
                $this->type='2';                
            }
        } else {
            $this->go='no';
            $this->type='2';
        }

            
    }

       public $dataNote;

    public function nowStemidData($stemid){
        // dd($stemid);
        $tagid=explode('.',$stemid);
        if(!isset($tagid[1])){$tagid[1]='0';}
        $data[0]=Ss10mTreeBase2024::where('tagid', 'like', $tagid[0])->first()->toArray();
        $data[0]['r']='n';
        $baseR=Ss10mTreeBaseR2024::where('stemid', 'like', $stemid)->get()->toArray();
        if (count($baseR)>0){
            $data[0]=$baseR[0];
            $data[0]['r']='y';
        }
        $data[0]['branch']=$tagid[1];
        $data[0]['stemid']=$stemid;
        if (isset($this->splist[$data[0]['spcode']])){
            $data[0]['csp']=$this->splist[$data[0]['spcode']];
        } else {
            $data[0]['csp']=$data[0]['spcode'];
        }
        
        if ($data[0]['deleted_at']!=''){
            $this->dataNote='此筆資料已被軟刪除';
        } else {
            $this->dataNote='';
        }
//獲取欄位名稱的陣列
        $census5 = Ss10mTreeData2024::query()->first()->toArray();
        // dd($census5);
        $keyarray = array_keys($census5);
        $stemid2=$tagid[0].$tagid[1];
        $stemid=$tagid[0].".".$tagid[1];
        $this->stemid2=$stemid2;
        $this->stemid=$stemid;



        for ($j = 1; $j <= 3; $j++) {
            $temp=[];

            switch ($j) {
                case '1':$table= new Ss10mTree2014; break;
                case '2':$table= new Ss10mTree2015; break;
                case '3':$table= new Ss10mTreeData2024; break;
            }
            
            $temp = $table::where('stemid', 'like', $stemid)->get()->toArray();


            if (count($temp)>0){

                foreach ($temp as $item) {
                    if ($j==1){
                        $item['date']='2014';
                    }
                    $data[$j] = [];
                    foreach ($keyarray as $key) {
                        $data[$j][$key] = $item[$key] ?? '';
                    }
                }

            } else {
                
                $data[$j] = array_fill_keys($keyarray, '');
                $data[$j]['stemid']=$stemid;
            }
            $censusyear[1]='2014';
            $censusyear[2]='2015';
            $censusyear[3]='2024';
            $data[$j]['census']=$censusyear[$j];
        }

        // dd($data);
        return $data;
                
    }


    public function fcsplist(Request $request){
    

            $ss10mcsplist=[];
            $splist=[];
           
            $csplist1 = Ss10mTreeRecord1::select('csp', DB::raw('count(stemid) as count2'))->groupBy('csp')->orderByDesc('count2')->get()->toArray();

            $splists=SsSplist::select('spcode', 'index')->get()->toArray();
            foreach($splists as $splist1){
                $splist[$splist1['spcode']]=$splist1['index'];
            }

            foreach ($csplist1 as $list) {
                $csplist2[]=$list['csp'];
            }

            foreach ($splists as $list){
                if (!in_array($list['index'], $csplist2)){
                    $csplist2[]=$list['index'];
                }
            }
        
            $ss10mcsplist=$csplist2;
            $request->session()->put('ss10mcsplist', $ss10mcsplist);


        $this->splist=$splist;
        $this->csplist=$ss10mcsplist;
    }



    public function render()
    {
        return view('livewire.shoushan.s10m-update');
    }
}
