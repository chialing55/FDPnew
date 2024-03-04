<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use App\Models\FsTreeEntrycom;
use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus5;

use App\Models\FsTreeBase;

use App\Jobs\FsTreeCensus5Progress;

class TreeUpdatetable extends Component
{



    public $comparelist=[];
    public $updatelist=[];
    public $qx;
    public $user;
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

    public $importnote;
    public $importnote2;

    public function import(Request $request){
        $qx=$this->qx;
        $keyarray=[];
        //census5資料表需先新增一筆(stemid=0)，以順利獲得資料欄位名稱
        $census5=FsTreeCensus5::query()->first()->toArray();
        $importdatas=FsTreeRecord1::where('qx', 'like', $qx)->get()->toArray();

        $basecol=FsTreeBase::query()->first()->toArray();
        //輸入資料不會更改base的資料，故只需要新增新增樹的資料
        
        //獲取欄位名稱的陣列
        $keyarray = array_keys($census5);
        // dd($keyarray);

        $basekeyarray = array_keys($basecol);
        $importnote2='';

        foreach ($importdatas as $importdata){

            $inlist=[];
            $inlist2=[];

            foreach ($keyarray as $key){
                if(isset($importdata[$key])){
                    $inlist[$key]=$importdata[$key];
                } else {
                    $inlist[$key]='';
                }
            }

            $inlist['update_id']=$this->user;
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
                }
                $inlist2['update_id']=$this->user;
                $inlist2['updated_at']=date("Y-m-d H:i:s");
                $inlist2['deleted_at']='';

                $baseRepeat=FsTreeBase::where('tag', 'like', $importdata['tag'])->get()->toArray();

                if (count($baseRepeat)>0){
                    $importnote2.='<br> tag '.$importdata['tag'].' 已存在，請檢查';
                } else {
                    FsTreeBase::insert($inlist2);
                }

            }
                //dd($inlist);

        
        $uplist=[];
        $uplist['census5update']=$this->user;
        $uplist['census5update_at']=date("Y-m-d H:i:s");

        FsTreeEntrycom::where('qx', 'like', $qx)->update($uplist);
        $this->reset();
        $this->importnote2=$importnote2;
        $this->importnote='已匯入 '.$qx." 線資料";
        $this->mount();


    }

    public function render()
    {
        return view('livewire.fushan.tree-updatetable');
    }
}
