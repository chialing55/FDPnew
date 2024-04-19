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
use App\Models\SsEntrycom;

use App\Jobs\TreeCompareCheck;
use App\Jobs\SsPlotFinishCheck;


class S10mCompare extends Component
{

    public $plots = array('B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38'); 

    public $plots1;
    public $plots1Array= array('BF', 'GF', 'QF', 'SF');

    public $entrycom1=[];
    public $entrycom2=[];

    public $entry1done;
    public $entry2done;
    public $comparedone;


    public function mount(){

        $this->plots1['BF'] = array('B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19');
        $this->plots1['GF'] = array('G-F-01', 'G-F-02', 'G-F-03', 'G-F-06');
        $this->plots1['QF'] = array('Q-F-03');
        $this->plots1['SF'] = array('S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38');

        $this->entrycom1 = Ss10mTreeRecord1::select('plot')->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->groupBy('plot')->pluck('plot')->toArray();
        $this->entrycom2 = Ss10mTreeRecord2::select('plot')->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->groupBy('plot')->pluck('plot')->toArray();
// dd($this->entrycom1);
        $entryFinish=SsEntrycom::query()->where('plot', 'like', '10m')->get()->toArray();
        if ($entryFinish[0]['entry1com']!=''){
            $this->entry1done='1';
        }
        if ($entryFinish[0]['entry2com']!=''){
            $this->entry2done='1';
        }
        if ($entryFinish[0]['compareOK']!=''){
            $this->comparedone='1';
        }
    }


    public $finishnote='';
    public $finishEntry='';


    public function entryFinish(Request $request, $entry){


        if ($entry==1){
            $table= new Ss10mTreeRecord1;
            $tabEnvi= new Ss10mTreeEnviR1;
            $col='entry1com';
        } else {
            $table= new Ss10mTreeRecord2;
            $tabEnvi= new Ss10mTreeEnviR2;
            $col='entry2com';
        }

        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        $pass='1';
        $finishnote='';

        $plotType='ss10m';

        $check = new SsPlotFinishCheck;
        $finishnote=$check->check($request, $tabEnvi, $table, $col, $plotType);

        if ($finishnote==''){
            $finishnote='通過檢查';
            $entrycomUpdate=SsEntrycom::query()->where('plot', 'like', '10m')->update([$col => '1', 'update_id'=>$user]);
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

        $this->finishEntry='';
        $this->finishnote='';
        $comnote='';
        $comnote1='';
        $comnote2='';
        $comnote3='';

    //比對環境資料
        $envi1 = Ss10mTreeEnviR1::query()->get()->keyBy('plot')->toArray();
        $envi2 = Ss10mTreeEnviR2::query()->get()->keyBy('plot')->toArray();
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

        $record1 = Ss10mTreeRecord1::query()->where('show', 'like', '1')->get()->keyBy('stemid')->toArray();
        $record2 = Ss10mTreeRecord2::query()->where('show', 'like', '1')->get()->keyBy('stemid')->toArray();
        $record1Stemid = array_keys($record1);
        $record2Stemid = array_keys($record2);

        $allStemid = array_unique(array_merge($record1Stemid, $record2Stemid));
        sort($allStemid);
        dd($record1['S-F-06-002.2']);
        $plotSize='10';
        $plotType='ss10m';

        $check = new TreeCompareCheck;
        $comnote2=$check->check($request, $record1, $record2, $allStemid, $plotSize, $plotType);

        if ($comnote2 !=''){
            $comnote.='<h6>每木資料比對</h6>';
            $comnote.=$comnote2."<br>";   //多空一行以示區隔
        }

//比對覆蓋度

        $cov1 = Ss10mTreeCovR1::query()->get()->map(function ($item) {
            // 將 plot、sqx、sqy 和 csp 欄位合併成一個新的 stemid 欄位
            $item['stemid'] = $item['plot'] . '-' . $item['sqx'] . '-' . $item['sqy'] . '-' . $item['csp'];
            unset($item['id']);
            return $item;
        })->keyBy('stemid')->toArray();

        $cov2 = Ss10mTreeCovR2::query()->get()->map(function ($item) {
            // 將 plot、sqx、sqy 和 csp 欄位合併成一個新的 stemid 欄位
            $item['stemid'] = $item['plot'] . '-' . $item['sqx'] . '-' . $item['sqy'] . '-' . $item['csp'];
            unset($item['id']);
            return $item;
        })->keyBy('stemid')->toArray();

        $cov1Stemid = array_keys($cov1);
        $cov2Stemid = array_keys($cov2);

        $plotType2='ss10mCov';

        $allcovStemid = array_unique(array_merge($cov1Stemid, $cov2Stemid));
        sort($allcovStemid);
        // dd($cov1);

        $check = new TreeCompareCheck;
        $comnote3=$check->check($request, $cov1, $cov2, $allcovStemid, $plotSize, $plotType2);

        if ($comnote3 !=''){
            $comnote.='<h6>地被資料比對</h6>';
            $comnote.=$comnote3."<br>";   //多空一行以示區隔
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
        return view('livewire.shoushan.s10m-compare');
    }
}
