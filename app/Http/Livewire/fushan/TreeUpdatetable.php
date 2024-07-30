<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use App\Models\FsTreeComplete;
use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus5;

use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;

use App\Jobs\FsTreeCensus5Progress;

class TreeUpdatetable extends Component
{



    public $comparelist=[];
    public $updatelist=[];
    public $qx;
    public $qx2;
    public $user;
    public $directories = [];

    public function mount(Request $request){

        $this->user = $request->session()->get('user', function () {
            return 'no';
        });

        $ob_result = new FsTreeCensus5Progress;
        $result=$ob_result->showProgress();

        // Extract only the directory names
        $this->directories = $result['directorieslist'];
        // dd($this->directories);
        $this->updatelist=$result['updatelist'];
        $this->comparelist=$result['comparelist'];
    }

    public $importnote;
    public $importnote2='';

//將資料匯入大表，census5
    public function import(Request $request){

        $qx=$this->qx;
        $keyarray=[];
        //census5資料表需先新增一筆(stemid=0)，以順利獲得資料欄位名稱
        $census5=FsTreeCensus5::query()->first()->toArray();
        $importdatas=FsTreeRecord1::where('qx', 'like', $qx)->get()->toArray();

        $basecol=FsTreeBase::query()->first()->toArray();
        $baseRcol=FsTreeBaseR::query()->first()->toArray();
        //輸入資料不會更改base的資料，故只需要新增新增樹的資料
        //但有些R會是這次才變R，需另加入
        
        //獲取欄位名稱的陣列
        $keyarray = array_keys($census5);
        // dd($keyarray);

        $basekeyarray = array_keys($basecol);
        $baseRkeyarray = array_keys($baseRcol);
        $importnote2='';

        foreach ($importdatas as $importdata){

            $inlist=[];
            $inlist2=[];
            $inlist3=[];

            foreach ($keyarray as $key){
                if(isset($importdata[$key])){
                    $inlist[$key]=$importdata[$key];
                } else {
                    $inlist[$key]='';
                }
            }

            $inlist['updated_id']=$this->user;
            $inlist['updated_at']=date("Y-m-d H:i:s");

            FsTreeCensus5::insert($inlist);

            if ($importdata['status']=='-9' && $importdata['branch']=='0'){
                 foreach ($basekeyarray as $key){
                    if(isset($importdata[$key])){
                        $inlist2[$key]=$importdata[$key];
                    } else {
                        $inlist2[$key]='0';
                    }
                }

                switch ($inlist2['sqx']) {
                    case '1':
                    case '2':
                        $inlist2['subqx'] = '1';
                        break;
                    case '3':
                    case '4':
                        $inlist2['subqx'] = '2';
                        break;
                    default:
                        $inlist2['subqx']; // 保留原始值
                        break;
                }

                switch ($inlist2['sqy']) {
                    case '1':
                    case '2':
                        $inlist2['subqy'] = '1';
                        break;
                    case '3':
                    case '4':
                        $inlist2['subqy'] = '2';
                        break;
                    default:
                        $inlist2['subqy']; // 保留原始值
                        break;
                }

                
                $inlist2['updated_id']=$this->user;
                $inlist2['updated_at']=date("Y-m-d H:i:s");
                $inlist2['deleted_at']='';

                $baseRepeat=FsTreeBase::where('tag', 'like', $importdata['tag'])->get()->toArray();

                if (count($baseRepeat)>0){
                    $importnote2.='<br> tag '.$importdata['tag'].' 已存在，請檢查';
                } else {
                    FsTreeBase::insert($inlist2);
                }
            }
//R
            if (strpos($importdata['code'], 'R') !== false){ 
            //strpos() 函數會傳回 "R" 在字符串中的位置，否則傳回 false。
                 foreach ($baseRkeyarray as $key){
                    if(isset($importdata[$key])){
                        $inlist3[$key]=$importdata[$key];
                    } else {
                        $inlist3[$key]='0';
                    }
                }

                switch ($inlist3['sqx']) {
                    case '1':
                    case '2':
                        $inlist3['subqx'] = '1';
                        break;
                    case '3':
                    case '4':
                        $inlist3['subqx'] = '2';
                        break;
                    default:
                        $inlist3['subqx']; // 保留原始值
                        break;
                }

                switch ($inlist3['sqy']) {
                    case '1':
                    case '2':
                        $inlist3['subqy'] = '1';
                        break;
                    case '3':
                    case '4':
                        $inlist3['subqy'] = '2';
                        break;
                    default:
                        $inlist3['subqy']; // 保留原始值
                        break;
                }

                $inlist3['updated_id']=$this->user;
                $inlist3['updated_at']=date("Y-m-d H:i:s");
                $inlist3['deleted_at']='';

                $baseRepeat=FsTreeBaseR::where('stemid', 'like', $importdata['stemid'])->get()->toArray();

                if (count($baseRepeat)>0){
                    // $importnote2.='<br> stemid '.$importdata['stemid'].' 已存在，請檢查';
                } else {
                    FsTreeBaseR::insert($inlist3);
                }
            }

        }
                //dd($inlist);

        
        $uplist=[];
        $uplist['addToMainTable']=$this->user;
        $uplist['addToMainTable_at']=date("Y-m-d H:i:s");

        FsTreeComplete::where('qx', 'like', $qx)->update($uplist);
        $this->reset();
        $this->importnote2=$importnote2;
        $this->importnote='已匯入 '.$qx." 線資料";
        $this->mount($request);


    }


//base_r表製作未完善，故以此法更新，暫保留，但已無用
    public function R(){

        $importdatas=FsTreeRecord1::where('qx', 'like', '18')->orWhere('qx', 'like', '19')->orWhere('qx', 'like', '20')->orWhere('qx', 'like', '21')->orWhere('qx', 'like', '22')->orWhere('qx', 'like', '23')->orWhere('qx', 'like', '24')->where('code', 'like', '%R%')->get()->toArray();
        $baseRcol=FsTreeBaseR::query()->first()->toArray();
        $baseRkeyarray = array_keys($baseRcol);

        foreach ($importdatas as $importdata){
//R
            if (strpos($importdata['code'], 'R') !== false){ 
            //strpos() 函數會傳回 "R" 在字符串中的位置，否則傳回 false。
                $baseRepeat=FsTreeBaseR::where('stemid', 'like', $importdata['stemid'])->get()->toArray();
                if (count($baseRepeat)==0){


                 foreach ($baseRkeyarray as $key){
                    if(isset($importdata[$key])){
                        $inlist3[$key]=$importdata[$key];
                    } else {
                        $inlist3[$key]='0';
                    }
                }

                switch ($inlist3['sqx']) {
                    case '1':
                    case '2':
                        $inlist3['subqx'] = '1';
                        break;
                    case '3':
                    case '4':
                        $inlist3['subqx'] = '2';
                        break;
                    default:
                        $inlist3['subqx']; // 保留原始值
                        break;
                }

                switch ($inlist3['sqy']) {
                    case '1':
                    case '2':
                        $inlist3['subqy'] = '1';
                        break;
                    case '3':
                    case '4':
                        $inlist3['subqy'] = '2';
                        break;
                    default:
                        $inlist3['subqy']; // 保留原始值
                        break;
                }

                $inlist3['updated_id']=$this->user;
                $inlist3['updated_at']=date("Y-m-d H:i:s");
                $inlist3['deleted_at']='';

                    FsTreeBaseR::insert($inlist3);
                }
            }

        }
                //dd($inlist);
    }


    public function render()
    {
        return view('livewire.fushan.tree-updatetable');
    }
}
