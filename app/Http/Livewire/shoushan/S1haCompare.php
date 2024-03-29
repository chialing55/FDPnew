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
use App\Models\SsEntrycom;

use App\Jobs\TreeCompareCheck;
use App\Jobs\SsPlotFinishCheck;


class S1haCompare extends Component
{

    public $entrycom1=[];
    public $entrycom2=[];

    public $entry1done;
    public $entry2done;


    public function mount(){

        $entrycom1=[];

        $entrycom1data = Ss1haRecord1::select('qx', 'qy')->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->groupBy('qx', 'qy')->get()->toArray();
        $entrycom2data = Ss1haRecord2::select('qx', 'qy')->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->groupBy('qx', 'qy')->get()->toArray();
        if($entrycom1data!=[]){
            foreach($entrycom1data as $item){
                $this->entrycom1[]=$item['qx']."-".$item['qy'];
            }
        }

        if($entrycom2data!=[]){
            foreach($entrycom2data as $item){
                $this->entrycom2[]=$item['qx']."-".$item['qy'];
            }
        }

        // dd($this->entrycom1);


        // $finishSite["'".$table['qx']."-".$table['qy']."'"]=$table['entry1'].$table['entry2'];


        $entryFinish=SsEntrycom::query()->where('plot', 'like', '1ha')->get()->toArray();
        if ($entryFinish[0]['entry1com']!=''){
            $this->entry1done='1';
        }
        if ($entryFinish[0]['entry2com']!=''){
            $this->entry2done='1';
        }

    }


    public $finishnote='';
    public $finishEntry='';


    public function entryFinish(Request $request, $entry){

        if ($entry==1){
            $table= new Ss1haRecord1;
            $tabEnvi= new Ss1haEnviR1;
            $col='entry1com';
        } else {
            $table= new Ss1haRecord2;
            $tabEnvi= new Ss1haEnviR2;
            $col='entry2com';
        }

        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        $pass='1';
        $finishnote='';

        $plotType='ss1ha';

        $check = new SsPlotFinishCheck;
        $finishnote=$check->check($request, $tabEnvi, $table, $col, $plotType);

        if ($finishnote==''){
            $finishnote='通過檢查';
            $entrycomUpdate=SsEntrycom::query()->where('plot', 'like', '1ha')->update([$col => '1', 'update_id'=>$user]);
            switch ($entry) {
                case '1':
                    $this->entry1done = 1;
                    break;
                case '2':
                    $this->entry2done = 1;
                    break;
            }
        }

        $this->finishnote=$finishnote;
        if($entry=='1'){
            $this->finishEntry='第一次輸入';
        } else {
            $this->finishEntry='第二次輸入';
        }
        
        $this->comnote='';
        
    }

    public $comnote='';

    public function compare(Request $request){
        $this->finishnote='';
        $comnote='';
        $comnote1='';
        $comnote2='';
        $comnote3='';

    //比對環境資料
        $envi1 = Ss1haEnviR1::query()->get()->keyBy('plot')->toArray();
        $envi2 = Ss1haEnviR2::query()->get()->keyBy('plot')->toArray();
        $envi1Keys = array_keys($envi1);
// dd($envi11[106]);
        // $this->envi1=$envi11;
        // $this->envi2=$envi2;
        $pass1='1';
        $arrayExculd=['update_id', 'updated_at'];

        foreach ($envi1Keys as $i => $key) {
            foreach ($envi1[$key] as $subKey => $value) {
                if (!in_array($subKey, $arrayExculd)) {
                    if ($envi2[$key][$subKey] !== $value) {
                        $comnote1 .= '環境資料比對: 樣區 ' . $key . ' 有資料不合。<br>';
                        $pass1 = '0';
                        break 2; // 跳出兩層迴圈
                    }
                }
            }
        }

        if ($pass1 == '0'){
            $comnote.='<h6>環境資料比對</h6>';
            $comnote.=$comnote1."<br>";   //多空一行以示區隔
        }

//比對每木資料

        $record1 = Ss1haRecord1::query()->where('show', 'like', '1')->get()->keyBy('stemid')->toArray();
        $record2 = Ss1haRecord2::query()->where('show', 'like', '1')->get()->keyBy('stemid')->toArray();
        $record1Stemid = array_keys($record1);
        $record2Stemid = array_keys($record2);

        $allStemid = array_unique(array_merge($record1Stemid, $record2Stemid));
        sort($allStemid);
        // dd($record1);
        $plotSize='10';
        $plotType='ss10m';

        $check = new TreeCompareCheck;
        $comnote2=$check->check($request, $record1, $record2, $allStemid, $plotSize, $plotType);

        if ($comnote2 !=''){
            $comnote.='<h6>每木資料比對</h6>';
            $comnote.=$comnote2."<br>";   //多空一行以示區隔
        }


        if ($comnote==''){
                $comnote='資料皆相符。恭喜比對完成。';
                $user = $request->session()->get('user', function () {
                    return 'no';
                });

                $uplist['compareOK']=date("Y-m-d H:i:s");
                $uplist['update_id']=$user;
                SsEntrycom::where('plot', 'like', '10m')->update($uplist);

        }


        $this->comnote=$comnote;


    }

    public function render()
    {
        return view('livewire.shoushan.s1ha-compare');
    }
}
