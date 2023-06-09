<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;


use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;

use App\Jobs\fsTreeAddButton;

class TreeShowentry extends Component
{

    public $entry;
    public $user;
    public $site;
    public $data;
    public $qx='0';
    public $qy='0';
    public $sqx;
    public $sqy;
    public $record;
    

    public function searchsite(Request $request, $qx, $qy, $sqx, $sqy){
        $this->entrynote='';
    // $request->session()->forget('csplist');
        $splist = $request->session()->get('splist');
        // dd($splist);

        if ($this->entry=='1'){
            $table= new FsTreeRecord1;
        } else {
            $table= new FsTreeRecord2;
        }

        $csplist=$request->session()->get('csplist', function () {
            return 'no';
        });
        // dd($qx, $qy);
        // 新增資料輸入種類用
        if ($csplist=='no'){
            $csplist=[];

            $csplist1 = $table::select('spcode', DB::raw('count(stemid) as count2'))->groupBy('spcode')->orderByDesc('count2')->get()->toArray();

            for($i=0;$i<count($csplist1);$i++){
                $csplist[$i]=$splist[$csplist1[$i]['spcode']];
            }
        
            $request->session()->put('csplist', $csplist);
        }

            $records=$table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('stemid', 'asc')->get()->toArray();



        if (!empty($records)){
            // dd($record);
            if ($records[0]['csp']==''){

                for($i=0;$i<count($records);$i++){
                    $record=$records[$i];
                    $update=[];
                    $records[$i]['csp']=$splist[$record['spcode']];
                    $update['csp']=$splist[$record['spcode']];
                    if ($record['status']=='-1'){
                        $census3=FsTreeCensus3::where('stemid', 'like', $record['stemid'])->get();
                        if ($census3[0]['status']=='-1'){
                            $update['show']='0';
                            $records[$i]['show']='0';
                        }
                    }

                    $table::where('stemid', 'like', $record['stemid'])->update($update);
                }
            }

            //新增樹為刪除按鍵，其他加入特殊修改按鍵

            $ob_redata = new fsTreeAddButton;
            $result=$ob_redata->addbutton($records, $this->entry);



        } else {
            $result='無';
        }

        //recruittable
        for($k=0;$k<20;$k++){
            $emptytable[$k]['qx']=$qx;
            $emptytable[$k]['qy']=$qy;
         
        }

        $this->record=$result;
        $this->qx=$qx;
        $this->qy=$qy;
        $this->sqx=$sqx;
        $this->sqy=$sqy;
    

        // $this->csplist=$csplist;
        // dd($result);

        $this->dispatchBrowserEvent('data', ['record' => $result,  'emptytable' => $emptytable, 'csplist' => $csplist]);
  

        // Livewire::emitTo('livewire.fushan.tree-showentry', '$refresh');

    }
    public $entrynote='';

//速度太慢，幫助不大，要從以輸入完成的資料表中取資料比較快
    public function qxchange($qx){
        // $this->record='';
        // if ($this->entry=='1'){
        //     $from=FsTreeRecord1::where('qx', 'like', $qx)->where('show', 'like', '1')->where('date', 'like', '0000-00-00')->orderBy('qy', 'asc')->get();
        // } else {

        // }
        
        // if (!$from->isEmpty()){
        //     $from1=$from[0]['qy'];
        //     $this->entrynote='* 可從 qy='.$from1."開始";
        // } else {
        //     $this->entrynote='* 樣線 '.$qx." 以輸入完成";
        // }
    }

    public function submitForm(Request $request){

        $this->searchsite($request, $this->qx, $this->qy, 1, 1);
    }


    public function render()
    {
        return view('livewire.fushan.tree-showentry');
    }
}
