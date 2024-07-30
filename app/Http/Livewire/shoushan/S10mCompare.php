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
use App\Models\Ss10mTreeCov2024;
use App\Models\Ss10mTreeEnviR1;
use App\Models\Ss10mTreeEnviR2;
use App\Models\Ss10mTreeCovR1;
use App\Models\Ss10mTreeCovR2;
use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;
use App\Models\SsSplist;
use App\Models\SsComplete;

use App\Jobs\TreeCompareCheck;
use App\Jobs\SsPlotFinishCheck;

//10m樣區資料比對
class S10mCompare extends Component
{

    public $plots = array('A1', 'A2', 'A3','B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19', 'G-F-01', 'G-F-02', 'G-F-03', 'G-F-06', 'Q-F-03', 'S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38'); 

    public $plots1;
    public $plots1Array= array('A','BF', 'GF', 'QF', 'SF');

    public $entrydone1=[];   //檢查第一次輸入資料表是否已完成
    public $entrydone2=[];  //檢查第二次輸入資料表是否已完成

    public $entry1done;   //輸入完成紀錄表中的紀錄
    public $entry2done;     //輸入完成紀錄表中的紀錄
    public $comparedone;
    public $user;


    public function mount(Request $request){

        $this->user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });


        $this->plots1['A'] = array('A1', 'A2', 'A3');
        $this->plots1['BF'] = array('B-F-01', 'B-F-04', 'B-F-06', 'B-F-13', 'B-F-14', 'B-F-19');
        $this->plots1['GF'] = array('G-F-01', 'G-F-02', 'G-F-03', 'G-F-06');
        $this->plots1['QF'] = array('Q-F-03');
        $this->plots1['SF'] = array('S-F-01', 'S-F-02', 'S-F-04', 'S-F-06', 'S-F-07', 'S-F-09', 'S-F-11', 'S-F-14', 'S-F-15', 'S-F-16', 'S-F-17', 'S-F-21', 'S-F-38');

        $this->entrydone1 = Ss10mTreeRecord1::select('plot')->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->groupBy('plot')->pluck('plot')->toArray();
        $this->entrydone2 = Ss10mTreeRecord2::select('plot')->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->groupBy('plot')->pluck('plot')->toArray();
// dd($this->entrydone1);
        $entryFinish=SsComplete::query()->where('plot', 'like', '10m')->get()->toArray();
        if ($entryFinish[0]['entry1Done']!=''){
            $this->entry1done='1';
        }
        if ($entryFinish[0]['entry2Done']!=''){
            $this->entry2done='1';
        }
        if ($entryFinish[0]['compareDone']!=''){
            $this->comparedone='1';
        }

        if (Schema::connection('mysql5')->hasTable('10m_tree_data_2024'))
        {
            $this->comparedone='0';
            $this->createTablenote='大表已建立';
        }

    }


    public $finishnote='';
    public $finishEntry='';
    public $loadingCompare;
    public $loadingEntryFinish;
    public $loadingCreateTable;

    public function entryFinish(Request $request, $entry){
        $this->loadingEntryFinish = true;
        $this->loadingCompare = false;
        $this->loadingCreateTable = false;

        if ($entry==1){
            $table= new Ss10mTreeRecord1;
            $tabEnvi= new Ss10mTreeEnviR1;
            $col='entry1Done';
        } else {
            $table= new Ss10mTreeRecord2;
            $tabEnvi= new Ss10mTreeEnviR2;
            $col='entry2Done';
        }


        $pass='1';
        $finishnote='';

        $plotType='ss10m';

        $check = new SsPlotFinishCheck;
        $finishnote=$check->check($request, $tabEnvi, $table, $col, $plotType);

        if ($finishnote==''){
            $finishnote='通過檢查';
            $entrycomUpdate=SsComplete::query()->where('plot', 'like', '10m')->update([$col => '1', 'updated_id'=>$this->user]);
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

        $this->loadingEntryFinish = false;
        $this->loadingCompare = true;
        $this->loadingCreateTable = false;

        // $this->finishEntry='';
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
        $arrayExculd=['updated_id', 'updated_at'];

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
        // dd($record1['S-F-06-002.2']);
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

                $uplist['compareDone']=date("Y-m-d H:i:s");
                $uplist['updated_id']=$user;
                SsComplete::where('plot', 'like', '10m')->update($uplist);

        }


        $this->comnote=$comnote;


    }
    public $createTablenote;
//將資料整理成大表，即不再透過輸入介面更動
    public function createTable(){

        $this->loadingEntryFinish = false;
        $this->loadingCompare = false;
        $this->loadingCreateTable = true;

        if (Schema::connection('mysql5')->hasTable('10m_tree_data_2024'))
        {
              $this->createTablenote='大表已建立';
        } else {
            DB::connection('mysql5')->select('CREATE TABLE 10m_tree_envi_2024 LIKE 10m_tree_envi_r1');
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_envi_2024 SELECT * FROM 10m_tree_envi_r1");

            DB::connection('mysql5')->select('CREATE TABLE 10m_tree_data_2024 LIKE 10m_tree_record1');
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_data_2024 SELECT * FROM 10m_tree_record1");
            //增加欄位
            DB::connection('mysql5')->statement("ALTER TABLE `10m_tree_data_2024` ADD (`deleted_at` CHAR(50) NOT NULL, `tagid` char(10) Not NULL)");
            DB::connection('mysql5')->statement("UPDATE `10m_tree_data_2024` left join `10m_tree_record1` on `10m_tree_data_2024`.stemid like `10m_tree_record1`.stemid set  `10m_tree_data_2024`.tagid = CONCAT(`10m_tree_record1`.plot,'-',`10m_tree_record1`.tag)");
            //刪除欄位
            DB::connection('mysql5')->statement("ALTER TABLE `10m_tree_data_2024` DROP COLUMN `csp`, DROP COLUMN `plot`, DROP COLUMN `sqx`, DROP COLUMN`sqy`");
            Ss10mTreeData2024::query()->update(['updated_id'=>'', 'updated_at'=>'']);

//cov
            DB::connection('mysql5')->select('CREATE TABLE 10m_tree_cov_2024 LIKE 10m_tree_cov_r1');
            Ss10mTreeCov2024::query()->update(['updated_id'=>'', 'updated_at'=>'']);
//base


            DB::connection('mysql5')->select('CREATE TABLE 10m_tree_base_2024 LIKE 10m_tree_base_2015');
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_base_2024 SELECT * FROM 10m_tree_base_2015");
//增加欄位
            DB::connection('mysql5')->statement("ALTER TABLE  `10m_tree_base_2024` ADD  (`deleted_at` char(50) not null, `qudx` float not null, `qudy` float not null)");
            DB::connection('mysql5')->select('CREATE TABLE 10m_tree_base_r_2024 LIKE 10m_tree_base_2024');
            DB::connection('mysql5')->select('ALTER TABLE `10m_tree_base_r_2024` DROP PRIMARY KEY');
            
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_base_r_2024 SELECT * FROM 10m_tree_base_2024 where tagid like 'B-F-01-001'");  //隨便先加一筆

            DB::connection('mysql5')->statement("ALTER TABLE  `10m_tree_base_r_2024` ADD  (`stemid` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci not null )");
            DB::connection('mysql5')->select('ALTER TABLE `10m_tree_base_r_2024` ADD PRIMARY KEY (`stemid`)');

            $splist=[];

            $splists=SsSplist::select('spcode', 'index')->get()->toArray();
            foreach($splists as $splist1){
                $splist[$splist1['index']]=$splist1['spcode'];
            }

            $importdatas=Ss10mTreeRecord1::query()->get()->toArray();

            $basecol=Ss10mTreeBase2024::query()->first()->toArray();
            $baseRcol=Ss10mTreeBaseR2024::query()->first()->toArray();
            //輸入資料不會更改base的資料，故只需要新增新增樹的資料
            //但有些R會是這次才變R，需另加入
            
            //獲取欄位名稱的陣列

            $basekeyarray = array_keys($basecol);
            $baseRkeyarray = array_keys($baseRcol);

            foreach ($importdatas as $importdata){
                if ($importdata['status']=='-9' && $importdata['branch']=='0'){
                     foreach ($basekeyarray as $key){
                        if(isset($importdata[$key])){
                            $inlist2[$key]=$importdata[$key];
                        } else {
                            $inlist2[$key]='0';
                        }
                    }
                    if (isset($splist[$importdata['csp']])){
                        $inlist2['spcode']=$splist[$importdata['csp']];
                    } else {
                        $inlist2['spcode']=$inlist2['csp'];
                    }
                    $importdata['tagid']=$importdata['plot'].'-'.$importdata['tag'];
                    $inlist2['tagid']=$importdata['tagid'];
                    $inlist2['updated_id']=$this->user;
                    $inlist2['updated_at']=date("Y-m-d H:i:s");
                    $inlist2['deleted_at']='';
                    
                    $baseRepeat=Ss10mTreeBase2024::where('tagid', 'like', $importdata['tagid'])->get()->toArray();

                    if (count($baseRepeat)>0){
                        $importnote2='<br> tagid '.$importdata['tagid'].' 已存在，請檢查';
                    } else {
                        Ss10mTreeBase2024::insert($inlist2);
                    }
                }

    //R/F
                if (stripos($importdata['code'], 'R') !== false || stripos($importdata['code'], 'F') !== false) {
                //strpos() 函數會傳回 "R/F" 在字符串中的位置，否則傳回 false。
                     foreach ($baseRkeyarray as $key){
                        if(isset($importdata[$key])){
                            $inlist3[$key]=$importdata[$key];
                        } else {
                            $inlist3[$key]='0';
                        }
                    }
                    if (isset($splist[$importdata['csp']])){
                        $inlist3['spcode']=$splist[$importdata['csp']];
                    } else {
                        $inlist3['spcode']=$inlist3['csp'];
                    }
                    $inlist3['updated_id']=$this->user;
                    $inlist3['updated_at']=date("Y-m-d H:i:s");
                    $inlist3['deleted_at']='';

                    $baseRepeat=Ss10mTreeBaseR2024::where('stemid', 'like', $importdata['stemid'])->get()->toArray();

                    if (count($baseRepeat)>0){
                        // $importnote2.='<br> stemid '.$importdata['stemid'].' 已存在，請檢查';
                    } else {
                        Ss10mTreeBaseR2024::insert($inlist3);
                    }
                }
            }

            Ss10mTreeBaseR2024::where('updated_id', 'like', '')->delete();

        }

        $this->createTablenote='大表已建立，請手動關閉輸入功能';
    }

    public function render()
    {
        return view('livewire.shoushan.s10m-compare');
    }
}
