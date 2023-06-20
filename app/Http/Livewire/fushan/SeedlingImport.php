<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingCov;
use App\Models\FsSeedlingBase;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;

class SeedlingImport extends Component
{
    public $slmaxcensus;
    public $nowcensus;
    public $importnote;
    public $user;
    public $site;

    public function mount(){

        $this->slmaxcensus=FsSeedlingData::max('census');
        $this->nowcensus=FsSeedlingSlrecord1::max('census');
        

    }


    public function import(){
//合併大表
        $s_seedling=FsSeedlingData::all()->toArray();
        // $s_seedling=$s_seedling->toArray();
        $seedlingkey=array_keys($s_seedling[0]);
        // dd($seedlingkey);

        $s_slrecord=FsSeedlingSlrecord1::all()->toArray();

        foreach($s_slrecord as $slrecord){
            $add=[];

            for($i=0;$i<count($seedlingkey);$i++){
                $add[$seedlingkey[$k]]=$slrecord[$seedlingkey[$k]];
            }

           // $insert=FsSeedlingData::insert($add);
        }

//cov
        $s_cov=FsSeedlingCov::all()->toArray();
        $covkey=array_keys($s_cov[0]);
        $s_slcov=FsSeedlingSlcov1::all()->toArray();

        foreach($s_slcov as $slcov){
            $add=[];

            for($i=0;$i<count($covkey);$i++){
                $add[$covkey[$k]]=$slcov[$covkey[$k]];
            }

           // $insert=FsSeedlingCov::insert($add);
        }        

//update_base

        $s_slbase=DB::connection('mysql3')->select('distinct mtag, trap, plot, x, y from slrecord1');
        $s_slbase=array_map(function ($value) { return (array)$value; }, $s_slbase);

        foreach($s_slbase as $slbase){
            $updatelist=[];
            $s_base=FsSeedlingBase::where('mtag', 'like', $slbase['mtag'])->get()->toArray();
            if (!empty($s_base)){  //有舊資料
                foreach ($s_base[0] as $key => $value){
                    if ($key !='id' && $key !='updated_at' && $key !='update_id'){
                        if ($s_base[0][$key]!=$slbase[$key]){
                            $updatelist[$key]=$value;
                        }
                    }
                }

                if (!empty($updatelist)){
                    $update=FsSeedlingBase::where('mtag', 'like', $slbase['mtag'])->update($updatelist);
                }
            } else {  //為新增資料
                foreach($slbase as $key => $value){
                    $insertlist[$key]=$value;
                }
                $insertlist['id']='';
                $insertlist['update_id']=$this->user;
                $insertlist['updated_at']=date("Y-m-d H:i:s");

                $insert=FsSeedlingBase::insert($insertlist);


            }
        }


//將cov1, slrecord1, slroll1改名


        $this->importnote="資料已匯入完成";


    }

    public function render()
    {
        return view('livewire.fushan.seedling-import');
    }
}
