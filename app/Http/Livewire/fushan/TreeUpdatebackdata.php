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
use App\Models\FsTreeComplete;
use App\Models\FsTreeCensus5;
use App\Models\FsTreeCensus1;
use App\Models\FsTreeCensus2;
use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;
use App\Models\FsTreeFixlog;

use App\jobs\FsTreeCensus5Progress;

//後端資料處理進度
class TreeUpdatebackdata extends Component
{

    public $qx;
    public $user;
    public $alternoteqxlist;
    public $addclass1='';
    public $addclass2='';
    public $alternotedone='';
    public $from;


    public function mount(Request $request){


        $alternoteqxlist = FsTreeCensus5::where('alternote', '!=', '')
            ->join('base', 'census5.tag', '=', 'base.tag')
            ->groupBy('base.qx')
            ->pluck('base.qx')
            ->toArray();

            // dd($alternoteqxlist);

        $this->alternoteqxlist=$alternoteqxlist;

        $alternotedone=FsTreeComplete::where('alternoteDone', '!=','')->groupBy('qx')->orderby('qx')->pluck('qx')->toArray();

        if (count($alternotedone)>0){
            foreach ($alternotedone as $value){
                $this->alternotedone.=$value." ";
            }
        }

        // dd($this->alternotedone);

        if (empty($this->csplist)){
            $this->fcsplist($request);
        }

    }


    public $csplist=[];
    public $splist=[];
    // public $alterdata=[];
    public $stemid;
    public $tag='';
    public $branch='';

    public $stemidlist=[];
    public $go;
    public $goto;
    public $type;
    public $filePath;

    public $finishnote;

//尋找需特殊修改的資料，依樣線
    public function alternote(Request $request){


        $qx=$this->qx;
        $this->stemidlist=[];


        $stemidlist=FsTreeCensus5::where('base.qx', 'like', $qx)->where('census5.alternote', '!=', '')->join('base', 'census5.tag', '=', 'base.tag')->orderBy('census5.stemid')->pluck('census5.stemid')->toArray();

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
//依stemid尋找資料
    public function updateStemidlist($data){
        $thisstemid = $data['thisstemid'];
        $from = $data['from'];

        if ($from=='alternote'){
            $stemidlist=FsTreeCensus5::where('base.qx', 'like', $this->qx)->where('census5.alternote', '!=', '')->join('base', 'census5.tag', '=', 'base.tag')->orderBy('census5.stemid')->pluck('census5.stemid')->toArray();
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

    public function alternoteFinish($qx){
        $uplist['alternoteDone']=$this->user;
        $uplist['alternoteDone_at']=date("Y-m-d H:i:s");
        FsTreeComplete::where('qx', 'like', $qx)->update($uplist);
        $this->finishnote="已記錄特殊修改完成";
        $this->searchStemid(0);
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
            $this->filePath=$stemiddata[0]['filePath'];
            $this->dispatchBrowserEvent('stemiddata', ['stemid'=>$this->stemid,'stemdata' => $stemiddata,  'csplist' => $this->csplist, 'from' => 'alternote']);
    }

    public function indStemid(Request $request){
        $tag=$this->tag;
        $b=$this->branch;
        $this->stemidlist=[];
        $this->qx='';
        $this->from='indStemid';
        // dd($tag);

        if ($tag!=''){

            $this->go='yes';
            $this->type='2';
            if ($b==''){$b='0';}
            $stemid=$tag.".".$b;
            //確認是否以匯入大表
            $census5 = FsTreeCensus5::where('stemid', 'like', $stemid)->get()->toArray();
            if (count($census5)>0){
            $this->stemidlist[0]=$stemid;
            $stemiddata=$this->nowStemidData($stemid);
            $this->filePath=$stemiddata[0]['filePath'];

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

//以stemid顯示所有資料表的該編號資料
    public function nowStemidData($stemid){
        // dd($stemid);
        $tag=explode('.',$stemid);
        if(!isset($tag[1])){$tag[1]='0';}
        $data[0]=FsTreeBase::where('tag', 'like', $tag[0])->first()->toArray();
        $data[0]['r']='n';
        $baseR=FsTreeBaseR::where('stemid', 'like', $stemid)->get()->toArray();
        if (count($baseR)>0){
            $datatemp[0]=$baseR[0];
            $datatemp[0]['spcode']=$data[0]['spcode'];
            $data[0]=$datatemp[0];
            $data[0]['r']='y';
        }
        $data[0]['branch']=$tag[1];
        $data[0]['stemid']=$stemid;
        $data[0]['csp']=$this->splist[$data[0]['spcode']];
        if ($data[0]['deleted_at']!=''){
            $this->dataNote='此筆資料已被軟刪除';
        } else {
            $this->dataNote='';
        }
//獲取欄位名稱的陣列
        $census5 = FsTreeCensus5::query()->first()->toArray();
        // dd($census5);
        $keyarray = array_keys($census5);
        $stemid2=$tag[0].$tag[1];
        $stemid=$tag[0].".".$tag[1];
        $this->stemid2=$stemid2;
        $this->stemid=$stemid;



        for ($j = 1; $j <= 5; $j++) {
            $temp=[];

            switch ($j) {
                case '1':$table= new FsTreeCensus1; break;
                case '2':$table= new FsTreeCensus2; break;
                case '3':$table= new FsTreeCensus3; break;
                case '4':$table= new FsTreeCensus4; break;
                case '5':$table= new FsTreeCensus5; break;
            }
            
            $temp = $table::where('stemid', 'like', $stemid)->get()->toArray();


            if (count($temp)>0){
                foreach ($temp as $item) {
                    $data[$j] = [];
                    foreach ($keyarray as $key) {
                        $data[$j][$key] = $item[$key] ?? '';
                    }
                }
                if ($j==1){
                    $data[$j]['h2']=$temp[0]['h'];
                }
            } else {
                
                $data[$j] = array_fill_keys($keyarray, '');
                $data[$j]['stemid']=$stemid;
            }

            $data[$j]['census']='census'.$j;
        }

        $filecensus='fs_census5_scanfile';
        $fileqx=str_pad($data[0]['qx'], 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($data[0]['qy'], 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;

        $filePath=[];

        $filePath[0]=$filecensus."/".$fileqx.'/old/'.$filesqx.'_old.pdf';
        $filePath[1]=$filecensus."/".$fileqx.'/new/'.$filesqx.'_new.pdf';
        $data[0]['filePath']=$filePath;

        // dd($data);
        return $data;
                
    }


    public function fcsplist(Request $request){
        //一進tree工作頁面就會產生splist
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

        
            session(['csplist' => $csplist]);
        }

        $this->splist=$splist;
        $this->csplist=$csplist;
    }




    public function render()
    {
        return view('livewire.fushan.tree-updatebackdata');
    }
}
