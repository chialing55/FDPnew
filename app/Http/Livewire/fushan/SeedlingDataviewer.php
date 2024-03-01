<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingBase;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;

class SeedlingDataviewer extends Component
{
    public $tag;
    public $allB= false;
    public $resultnote;
    public $basedata;
    public $result;
    public $lastCensus;
    public $tableTag;

    public function mount(Request $request)
    {
        $lastCensus=FsSeedlingSlrecord1::first();
        $this->lastCensus=$lastCensus['census'];
    }



    public function submitTagForm(Request $request)
    {
     //   dd($this->allB);
        $this->serachTag($request, $this->tag, $this->allB);
    }

    public function serachTag(Request $request, $tag, $allB)
    {
        // dd($tag);
        // $this->tag=$tag;
        $tag = str_pad($tag, 4, '0', STR_PAD_LEFT);
        $mtag = explode('.', $tag)[0];
        if (isset(explode('.', $tag)[1])){
            $branch= explode('.', $tag)[1];
        } else {
            $branch='0';
        }
        $this->tableTag=$mtag.$branch;
        $this->tag=$tag;


        $result=FsSeedlingData::where('tag', 'like', $tag)->orderBy('census', 'ASC')->get()->toArray();
        // $result2=FsSeedlingData::where('mtag', 'like', $mtag)->orderBy('census', 'ASC')->get()->toArray();

        $resultRecord1=FsSeedlingSlrecord1::where('tag', 'like', $tag)->get()->toArray();
        $resultRecord2=FsSeedlingSlrecord2::where('tag', 'like', $tag)->get()->toArray();


       // dd($result);
        if ($result==[]){

            if ($resultRecord1!=[]){
                $result=$resultRecord1;
                $result2=FsSeedlingSlrecord1::where('mtag', 'like', $mtag)->get()->toArray();
                $basedata=FsSeedlingSlrecord1::where('mtag','like', $mtag)->first();
            } else if ($resultRecord2!=[]){
                $result=$resultRecord2;
                $result2=FsSeedlingSlrecord2::where('mtag', 'like', $mtag)->get()->toArray();
                $basedata=FsSeedlingSlrecord2::where('mtag','like', $mtag)->first();
            } 
        } else {
            $result2=FsSeedlingData::where('mtag', 'like', $mtag)->orderBy('census', 'ASC')->get()->toArray();
            $basedata=FsSeedlingBase::where('mtag','like', $mtag)->first();

            if ($resultRecord1!=[]){

                $sourceRecord = $resultRecord1[0]['updated_at'] != '' ? $resultRecord1[0] : $resultRecord2[0];

                foreach ($result[0] as $key => $value) {
                    $result[$q][$key] = $sourceRecord[$key];
                    $result[$q]['note']=$sourceRecord['note']." ".$sourceRecord['alternote'];
                }
            }

        }

        if ($result==[]){
                $this->resultnote='查無此苗';
        } else {
            $this->resultnote='';
            $q=count($result);



        // dd($result);

            $b=0;
            foreach ($result2 as $res){
                $b1=explode('.',$res['tag']);

                if(!isset($b1[1])){$b1[1]='0';}

                if ($b1[1]>$b){
                    $b=$b1[1];
                }
            }

            $this->result=$result;


            // dd($basedata);
            $basedata['maxb']=$b;
            $basedata['csp']=$result[0]['csp'];

            $this->basedata=$basedata;


            $this->dispatchBrowserEvent('initTablesorter', ['tag' => $this->tableTag]);        
        }


    }
    // public $showTable = false;
    // protected $listeners = ['tagUpdated'];

    // public function tagUpdated($tag)
    // {
    //     // 设置 tag 后显示表格
    //     $this->showTable = true;
    //     // 调用 JavaScript 初始化 tablesorter
    //     $this->dispatchBrowserEvent('initTablesorter');
    // }


    public function render()
    {
        return view('livewire.fushan.seedling-dataviewer');
    }
}
