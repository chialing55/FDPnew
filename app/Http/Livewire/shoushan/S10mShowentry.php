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
use App\Models\Ss10mTreeEnviR1;
use App\Models\Ss10mTreeEnviR2;
use App\Models\Ss10mTreeCovR1;
use App\Models\Ss10mTreeCovR2;
use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;
use App\Models\SsSplist;

use App\Jobs\TreeAddButton;

class S10mShowentry extends Component
{
    public $entry;
    public $user;
    public $site;

    public $plots = array('A1', 'A2', 'A3', 'B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38');     


    public $selectPlot;
    public $record;
    public $entrynote;
    public $sqx;
    public $sqy;
    public $cov;

    public function mount(){

        if ($this->entry == '1') {
            $table= new Ss10mTreeRecord1;
            $tablecov= new Ss10mTreeCovR1;
            $tableenvi= new Ss10mTreeEnviR1;
            $entryother='2';
        } else {
            $table= new Ss10mTreeRecord2;
            $tablecov= new Ss10mTreeCovR2;
            $tableenvi= new Ss10mTreeEnviR2;
            $entryother='1';
        }

        $data=$table::where('date', 'like', '0000-00-00')->get()->toArray();

        if (count($data)==0){
            $this->entrynote='第'.$this->entry.'次輸入已完成。 若以完成第'.$entryother.'次輸入，可進行資料比對。';
        }

    }
//選擇輸入樣區
    public function searchSite(Request $request, $selectPlot, $sqx, $sqy){

        $plot=$this->plots[$selectPlot];

        if ($this->entry == '1') {
            $table= new Ss10mTreeRecord1;
            $tablecov= new Ss10mTreeCovR1;
            $tableenvi= new Ss10mTreeEnviR1;
            $entryother='2';
        } else {
            $table= new Ss10mTreeRecord2;
            $tablecov= new Ss10mTreeCovR2;
            $tableenvi= new Ss10mTreeEnviR2;
            $entryother='1';
        }

        // dd($qx, $qy);
        // 新增資料輸入種類用

        $ss10mcsplist=$request->session()->get('ss10mcsplist', function () {
            return 'no';
        });
        $ss10mcovcsplist=$request->session()->get('ss10mcsplist', function () {
            return 'no';
        });

        if ($ss10mcsplist=='no'){
            $ss10mcsplist=[];
            $ss10mcovcsplist=[];

            $csplist1 = $table::select('csp', DB::raw('count(stemid) as count2'))->groupBy('csp')->orderByDesc('count2')->get()->toArray();

            $splist=SsSplist::select('index')->orderBy('index', 'asc')->get()->toArray();

            foreach ($csplist1 as $list) {
                $csplist2[]=$list['csp'];
            }

            foreach ($splist as $list){
                if (!in_array($list['index'], $csplist2)){
                    $csplist2[]=$list['index'];
                }

                $covcsplist2[]=$list['index'];
            }
        
            $ss10mcsplist=$csplist2;
            $ss10mcovcsplist=$covcsplist2;
            $request->session()->put('ss10mcsplist', $ss10mcsplist);
            $request->session()->put('ss10mcovcsplist', $ss10mcovcsplist);

        } 
        

          
        

// dd($ss10mcsplist);


        $envi=$tableenvi::query()->where('plot', 'like', $plot)->get()->toArray();
        $records=$table::query()->where('plot', 'like', $plot)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get()->toArray();
        $cov=$tablecov::query()->where('plot', 'like', $plot)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->orderBy('sqx', 'asc')->orderBy('sqy', 'asc')->orderBy('layer', 'desc')->orderBy('id', 'asc')->get()->toArray();

        //新增樹為刪除按鍵，其他加入特殊修改按鍵
        if (count($records)>0){

            $ob_redata = new TreeAddButton;
            $result=$ob_redata->addbutton($records, $this->entry);
        } else {
            $result='無';
        }

        if (count($cov)>0){

            for($m=0;$m<count($cov);$m++){

            // HTML 輸出編碼
            $deleteid = htmlspecialchars($cov[$m]['id']);
            $escapedEntry = htmlspecialchars($this->entry);


                $cov[$m]['delete']="<button class='deletecov' onclick='deletecov(\"$deleteid\", \"$escapedEntry\")' deleteid='".$cov[$m]['id']."' entry='".$this->entry."' plot='".$plot."'>X</button>";
 
        }} 


        $this->record=$result;
        $this->cov=$cov;
        $this->selectPlot=$selectPlot;
        $this->covsavenote='';
        $this->datasavenote='';
        $this->plot=$plot;
        $this->sqx=$sqx;
        $this->sqy=$sqy;

        //covtable 
        for($k=0;$k<20;$k++){
            $emptytable2[$k]['date']='';
            $emptytable2[$k]['plot']=$plot;
            $emptytable2[$k]['sqx']='';
            $emptytable2[$k]['sqy']='';
            $emptytable2[$k]['layer']='';
            $emptytable2[$k]['csp']='';
            $emptytable2[$k]['cover']='';
            $emptytable2[$k]['height']='';
            $emptytable2[$k]['note']='';
        }   

        //recruittable
        for($k=0;$k<30;$k++){
            $emptytable[$k]['plot']=$plot;
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

        $site=$plot.$sqx.$sqy;

        $this->dispatchBrowserEvent('data', ['covs' => $cov, 'record' => $result, 'emptytable' => $emptytable,'emptytable2' => $emptytable2, 'csplist' => $ss10mcsplist, 'covcsplist' => $ss10mcovcsplist, 'envi' => $envi, 'site'=> $site]);

    }

    public function submitsqxForm(Request $request){

        $this->searchsite($request, $this->selectPlot, $this->sqx, $this->sqy);
    }


    public function render()
    {
        return view('livewire.shoushan.s10m-showentry');
    }
}
