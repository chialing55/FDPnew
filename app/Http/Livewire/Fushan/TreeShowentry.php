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
use App\Models\FsTreeCensus5;


use App\Jobs\TreeAddButton;
use App\Jobs\FsTreeCensus5Progress;

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

    public $comparelist=[];
    public $updatelist=[];
    public $directories = [];

    public function mount(){

        $ob_result = new FsTreeCensus5Progress;
        $result=$ob_result->showProgress();

        // Extract only the directory names
        $this->directories = $result['directorieslist'];
        // dd($this->directories);
        $this->updatelist=$result['updatelist'];
        $this->comparelist=$result['comparelist'];
    }



//選擇要輸入資料的樣區    

    public function searchSite(Request $request, $qx, $qy, $sqx, $sqy){
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

            foreach ($splist as $key=>$value){
                if (!in_array($value, $csplist)){
                    $csplist[]=$value;
                }
            }

        
            $request->session()->put('csplist', $csplist);
        }

            $records=$table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->orderBy('stemid', 'asc')->get()->toArray();
            $records_1=$table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('date','like', '0000-00-00')->where('show', 'like', '1')->get()->toArray();
            // dd($records);

        if (count($records)>0){
            // dd($records);
            if (count($records_1)>0){
                // 這個樣區有尚未輸入完成的資料

                for($i=0;$i<count($records);$i++){
                    $record=$records[$i];
                    $update=[];
                    // $records[$i]['csp']=$splist[$record['spcode']];
                    $update['csp']=$splist[$record['spcode']];
                    if ($record['date']=='0000-00-00'){ //還未輸入

                        //依據census4把code填入
                        $census4=FsTreeCensus4::where('stemid', 'like', $record['stemid'])->get();
                        if (count($census4)>0){
                            if ($census4[0]['code']!=''){
                                $update['code']=$census4[0]['code'];
                                // $records[$i]['code']=$census4[0]['code'];
                            }                    

                        }

                        if ($record['status']=='-1'){
                        
                        // 把census3=-1的show改為0
                        // 前兩次調查已為 -1 的植株，show=0 
          
                            $census3=FsTreeCensus3::where('stemid', 'like', $record['stemid'])->get();
                            if ($census3[0]['status']=='-1'){
                                $update['show']='0';
                                // $records[$i]['show']='0';
                            } 
                        }

                    }
                    $table::where('stemid', 'like', $record['stemid'])->update($update);
                }
            }

            //重新載入records

            $records1=$table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get()->toArray();

            //新增樹為刪除按鍵，其他加入特殊修改按鍵
            if (count($records1)>0){

                for($i=0;$i<count($records1);$i++){
                    if ($records1[$i]['tag'][0]=='G'){
                        $records1[$i]['dbh']=$records1[$i]['h2'];
                    }
                }

                $ob_redata = new TreeAddButton;
                $result=$ob_redata->addbutton($records1, $this->entry);
            } else {
                $result='無';
            }
            //拆解alternote


        } else {
            $result='無';
        }

        //recruittable
        for($k=0;$k<30;$k++){
            $emptytable[$k]['qx']=$qx;
            $emptytable[$k]['qy']=$qy;
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

        $this->record=$result;
        $this->qx=$qx;
        $this->qy=$qy;
        $this->sqx=$sqx;
        $this->sqy=$sqy;
        

        // $this->csplist=$csplist;
        // dd($result);

        $this->dispatchBrowserEvent('data', ['record' => $result,  'emptytable' => $emptytable, 'csplist' => $csplist]);
        $this->dispatchBrowserEvent('rePlotsentry', ['sqx'=>$sqx, 'sqy'=>$sqy]);

        // Livewire::emitTo('livewire.fushan.tree-showentry', '$refresh');

    }
    public $entrynote='';


    public function submitForm(Request $request){

        $this->searchSite($request, $this->qx, $this->qy, 1, 1);
    }


    public function submitsqxForm(Request $request, $sqx, $sqy){

        $this->searchSite($request, $this->qx, $this->qy, $sqx, $sqy);
    }

    public function render()
    {
        return view('livewire.fushan.tree-showentry');
    }
}
