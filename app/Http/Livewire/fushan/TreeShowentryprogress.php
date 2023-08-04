<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsTreeEntrycom;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsBaseLogin;


class TreeShowentryprogress extends Component
{

    public $finishSite=[];
    public $countFinishSite1=0;
    public $countFinishSite2=0;
    public $countFinishSiteall=0;
    public $entrytable;

    public function mount(){

//輸入完成表
        $tables=FsTreeEntrycom::get()->toArray();
        $countFinishSite1=0;
        $countFinishSite2=0;
        $countFinishSiteall=0;
        

        foreach($tables as $table){
            $finishSite["'".$table['qx']."-".$table['qy']."'"]=$table['entry1'].$table['entry2'];

            if ($table['entry1']==1){$countFinishSite1=$countFinishSite1+1;}
            if ($table['entry2']==1){$countFinishSite2=$countFinishSite2+1;}
            if ($table['entry1']==1 && $table['entry2']==1){
                $countFinishSiteall=$countFinishSiteall+1;
            }
        }

        $this->finishSite=$finishSite;
        $this->countFinishSiteall=$countFinishSiteall;
        $this->countFinishSite1=$countFinishSite1;
        $this->countFinishSite2=$countFinishSite2;

        $names=FsBaseLogin::all()->toArray();
        for ($i=0; $i<count($names); $i++){
            $userid[$names[$i]['id2']]=$names[$i]['name'];
        }

//助理進度表


        $table1s=FsTreeRecord1::select('update_id', DB::raw('LEFT(updated_at, 10) AS date1'), DB::raw('count(stemid) as pps'))->where('date', 'not like', '0000-00-00')->groupBy('update_id', 'date1')->orderByDesc('date1')->get()->toArray();
        for($i=0;$i<count($table1s); $i++){
            
            if (isset($userid[$table1s[$i]['update_id']])){
                $table1s[$i]['name']=$userid[$table1s[$i]['update_id']];
            } else {
                $table1s[$i]['name']='';
            }
            $tableall[$table1s[$i]['date1']][$table1s[$i]['update_id']]=$table1s[$i];
        }

        // dd($tableall);
        $table2s=FsTreeRecord2::select('update_id', DB::raw('LEFT(updated_at, 10) AS date1'), DB::raw('count(stemid) as pps'))->where('date', 'not like', '0000-00-00')->groupBy('update_id', 'date1')->get()->toArray();

        if (count($table2s)>0){
            for($i=0;$i<count($table2s); $i++){
                
                if (isset($userid[$table2s[$i]['update_id']])){
                    $table2s[$i]['name']=$userid[$table2s[$i]['update_id']];
                } else {
                    $table2s[$i]['name']='';
                }

                if (isset($tableall[$table2s[$i]['date1']][$table2s[$i]['update_id']])){
                    $tableall[$table2s[$i]['date1']][$table2s[$i]['update_id']]['pps']=$table2s[$i]['pps']+$tableall[$table2s[$i]['date1']][$table2s[$i]['update_id']]['pps'];
                } else {
                    $tableall[$table2s[$i]['date1']][$table2s[$i]['update_id']]=$table2s[$i];
                }
                
            }
        }
        // dd($tableall);
        $this->entrytable=$tableall;


    }





    public function render()
    {
        return view('livewire.fushan.tree-showentryprogress');
    }
}
