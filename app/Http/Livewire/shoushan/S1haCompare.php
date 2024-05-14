<?php

namespace App\Http\Livewire\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Schema;

use App\Models\Ss1haData2015;
use App\Models\Ss1haData2024;
use App\Models\Ss1haBase2015;
use App\Models\Ss1haBase2024;
use App\Models\Ss1haBaseR2024;
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
    public $comparedone;
    public $user;

    public function mount(Request $request){

        $this->user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });

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
        if ($entryFinish[0]['compareOK']!=''){
            $this->comparedone='1';
        }

        if (Schema::connection('mysql5')->hasTable('1ha_data_2024'))
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
            $table= new Ss1haRecord1;
            $tabEnvi= new Ss1haEnviR1;
            $col='entry1com';
        } else {
            $table= new Ss1haRecord2;
            $tabEnvi= new Ss1haEnviR2;
            $col='entry2com';
        }


        $pass='1';
        $finishnote='';

        $plotType='ss1ha';

        $check = new SsPlotFinishCheck;
        $finishnote=$check->check($request, $tabEnvi, $table, $col, $plotType);

        if ($finishnote==''){
            $finishnote='通過檢查';
            $entrycomUpdate=SsEntrycom::query()->where('plot', 'like', '1ha')->update([$col => '1', 'update_id'=>$this->user]);
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

                $uplist['compareOK']=date("Y-m-d H:i:s");
                $uplist['update_id']=$this->user;
                SsEntrycom::where('plot', 'like', '1ha')->update($uplist);

        }


        $this->comnote=$comnote;


    }


    public $createTablenote;

    public function createTable(){

        $this->loadingEntryFinish = false;
        $this->loadingCompare = false;
        $this->loadingCreateTable = true;

        if (Schema::connection('mysql5')->hasTable('1ha_data_2024'))
        {
              $this->createTablenote='大表已建立';
        } else {
            DB::connection('mysql5')->select('CREATE TABLE 1ha_envi_2024 LIKE 1ha_envi_r1');
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 1ha_envi_2024 SELECT * FROM 1ha_envi_r1");

            DB::connection('mysql5')->select('CREATE TABLE 1ha_data_2024 LIKE 1ha_record1');
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 1ha_data_2024 SELECT * FROM 1ha_record1");
            //增加欄位
            DB::connection('mysql5')->statement("ALTER TABLE `1ha_data_2024` ADD COLUMN `deleted_at` CHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
            //刪除欄位
            DB::connection('mysql5')->statement("ALTER TABLE `1ha_data_2024` DROP COLUMN `csp`, DROP COLUMN `qx`, DROP COLUMN `qy`, DROP COLUMN `sqx`, DROP COLUMN`sqy`");
            Ss1haData2024::query()->update(['update_id'=>'', 'updated_at'=>'']);


            DB::connection('mysql5')->select('CREATE TABLE 1ha_base_2024 LIKE 1ha_base_2015');
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 1ha_base_2024 SELECT * FROM 1ha_base_2015");
//增加欄位
            DB::connection('mysql5')->statement("ALTER TABLE  `1ha_base_2024` ADD  (`deleted_at` char(50) not null, `update_id` char(20) not null, `updated_at` char(200) CHARACTER SET utf8 COLLATE utf8_general_ci not null )");

            DB::connection('mysql5')->select('CREATE TABLE 1ha_base_r_2024 LIKE 1ha_base_2024');
            DB::connection('mysql5')->select('ALTER TABLE `1ha_base_r_2024` DROP PRIMARY KEY');
            
            DB::connection('mysql5')->statement("INSERT IGNORE INTO 1ha_base_r_2024 SELECT * FROM 1ha_base_2024 where tag like '10001'");  //隨便先加一筆

            DB::connection('mysql5')->statement("ALTER TABLE  `1ha_base_r_2024` ADD  (`stemid` char(10) CHARACTER SET utf8 COLLATE utf8_general_ci not null )");
            DB::connection('mysql5')->select('ALTER TABLE `1ha_base_r_2024` ADD PRIMARY KEY (`stemid`)');

            $splist=[];

            $splists=SsSplist::select('spcode', 'index')->get()->toArray();
            foreach($splists as $splist1){
                $splist[$splist1['index']]=$splist1['spcode'];
            }

            $importdatas=Ss1haRecord1::query()->get()->toArray();

            $basecol=Ss1haBase2024::query()->first()->toArray();
            $baseRcol=Ss1haBaseR2024::query()->first()->toArray();
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
                    
                    $inlist2['update_id']=$this->user;
                    $inlist2['updated_at']=date("Y-m-d H:i:s");
                    $inlist2['deleted_at']='';

                    $baseRepeat=Ss1haBase2024::where('tag', 'like', $importdata['tag'])->get()->toArray();

                    if (count($baseRepeat)>0){
                        $importnote2.='<br> tag '.$importdata['tag'].' 已存在，請檢查';
                    } else {
                        Ss1haBase2024::insert($inlist2);
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
                    $inlist3['update_id']=$this->user;
                    $inlist3['updated_at']=date("Y-m-d H:i:s");
                    $inlist3['deleted_at']='';

                    $baseRepeat=Ss1haBaseR2024::where('stemid', 'like', $importdata['stemid'])->get()->toArray();

                    if (count($baseRepeat)>0){
                        // $importnote2.='<br> stemid '.$importdata['stemid'].' 已存在，請檢查';
                    } else {
                        Ss1haBaseR2024::insert($inlist3);
                    }
                }
            }

            Ss1haBaseR2024::where('update_id', 'like', '')->delete();

        }

        $this->createTablenote='大表已建立，請手動關閉輸入功能';
    }

    public function render()
    {
        return view('livewire.shoushan.s1ha-compare');
    }
}
