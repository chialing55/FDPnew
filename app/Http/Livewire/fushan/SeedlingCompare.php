<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;

class SeedlingCompare extends Component
{

    public $comnote;

    public function compare(){

// 確認是否輸入完資料
        $cov=FsSeedlingSlcov1::where('date', 'like', '0000-00-00')->get();
        if (!$cov->isEmpty()){
            $comnote='第一次輸入尚未完成，請確認輸入完成後再進行比對。';
            // break;
        } else {

            $cov=FsSeedlingSlcov2::where('date', 'like', '0000-00-00')->get();
            if (!$cov->isEmpty()){
                $comnote='第二次輸入尚未完成，請確認輸入完成後再進行比對。';
                // break;
            } else {
                $data=FsSeedlingSlrecord1::where('date', 'like', '0000-00-00')->get();
                if (!$data->isEmpty()){
                    $comnote='第一次輸入尚未完成，請確認輸入完成後再進行比對。';
                } else {
                    $data=FsSeedlingSlrecord2::where('date', 'like', '0000-00-00')->get();
                    if (!$data->isEmpty()){
                        $comnote='第二次輸入尚未完成，請確認輸入完成後再進行比對。';
                    } 
                }
            }
       }
       if ($comnote==''){  //皆輸入完成

        //比對環境資料
            $cov1=FsSeedlingSlcov1::orderBy('trap', 'asc')->orderBy('plot', 'asc');
            $cov1=$cov1->toArray();
            foreach($cov1 as $cov){
                $cov['update_id']=='';
                $cov['updated_at']=='';
            }

            $cov2=FsSeedlingSlcov2::orderBy('trap', 'asc')->orderBy('plot', 'asc');
            $cov2=$cov2->toArray();
            foreach($cov2 as $cov){
                $cov['update_id']=='';
                $cov['updated_at']=='';
            }
            $comnote='';

            for ($j=1;$j<108;$j++){ 
                for ($i=0; $i<count($cov1[$j]);$i++){
                foreach ($cov1[$j][$i] as $key => $value){
                        if ($cov2[$j][$i][$key]!=$value){
                            $comnote=$comnote.'環境資料比對: 樣站 '.$j.' 有資料不合。<br>';
                            $path='N';
                            break;
                        } else {$path='Y';}
                        
                    }
                if ($path=='N'){break;}
                }
            }

            $comnote=$comnote."<br>";

            // 比對小苗資料

            for ($j=1;$j<108;$j++){

                $tag1=array();
                $tag2=array();
                $record1=array();
                $record2=array();

                
                $s_record1=FsSeedlingSlrecord1::where('trap', 'like', $j)->get();
                if (!$s_record1->isEmpty()){
                    $s_record1=$s_record1->toArray();
                    foreach ($s_record1 as $record){
                        $record['update_id']='';
                        $record['updated_at']='';
                        $record['id']='';
                        $tag1[]=$record['tag'];
                        $record1[$record['tag']]=$record;
                    }
                }

                $s_record2=FsSeedlingSlrecord2::where('trap', 'like', $j)->get();
                if (!$s_record2->isEmpty()){
                    $s_record2=$s_record2->toArray();
                    foreach ($s_record2 as $record){
                        $record['update_id']='';
                        $record['updated_at']='';
                        $record['id']='';
                        $tag1[]=$record['tag'];
                        $record2[$record['tag']]=$record;
                    }
                }


                if (count($tag1)>0){
                //列出所有的tag
                $tag2 = array_unique($tag1);
                $tag2 = array_diff($tag2, array(""));

                sort($tag2);    
                    

                    //依tag比對

                    //$comnote=$comnote.$j.'step3<br>';
                    for ($i=0; $i<count($tag2);$i++){
                    if (isset($record1[$tag2[$i]])){
                    //$comnote=$comnote.'step4<br>';
                        if (isset($record2[$tag2[$i]])){
                        //$comnote=$comnote.'step5<br>';
                        foreach ($record1[$tag2[$i]] as $key => $value){
                            if ($record2[$tag2[$i]][$key]!=$value){
                                
                               
                                // $add2=" [".$key.", (".$value."), (".$record2[$tag2[$i]][$key].")]";  
                                $add2='['.$key.']';
                                
                                $comnote=$comnote.'小苗資料比對: 樣站 '.$j.' No. '.$tag2[$i].$add2.' 的資料不合。<br>';             
                                
                            }
                            
                        }
                    } else {  //1有2沒有
                        
                            $comnote=$comnote.'小苗資料比對: 樣站 '.$j.' No. '.$tag2[$i].' 第二次輸入缺資料。<br>';    
                    }
                    
                    
                    
                    } else {  //1沒有2有
                            $comnote=$comnote.'小苗資料比對: 樣站 '.$j.' No. '.$tag2[$i].' 第一次輸入缺資料。<br>';    
                    }
                }
                }}
                $comnote=$comnote."<br>";

// 比對撿到環資料
                $s_roll1=FsSeedlingSlroll1::orderBy('trap', 'asc')->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();

                if (!$s_roll1->isEmpty()){
                    $s_roll1=$s_roll1->toArray();
                    foreach($s_roll1 as $roll){
                        $roll['id']='';
                        $roll['update_id']='';
                        $roll['updated_at']='';
                        $roll1[]=$roll;
                    }

                }
                $s_roll2=FsSeedlingSlroll2::orderBy('trap', 'asc')->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();

                if (!$s_roll2->isEmpty()){
                    $s_roll2=$s_roll2->toArray();
                    foreach($s_roll2 as $roll){
                        $roll['id']='';
                        $roll['update_id']='';
                        $roll['updated_at']='';
                        $roll2[]=$roll;
                    }

                }

                    //比對

                    
                    for ($i=0; $i<count($roll1);$i++){
                        foreach ($roll1[$i] as $key => $value){
                            if ($roll2[$i][$key]!=$value){
                                
                                
                                $add3=" [".$key.", (".$value."), (".$roll2[$i][$key].")]";  
                                // $add3='['.$key.']';
                                $comnote=$comnote.'撿到環資料比對: 樣站 '.$roll1[$i]['trap'].' No. '.$roll1[$i]['tag'].' 的 '.$add3.' 資料不合。<br>';
                                break;
                            }
                            
                        }
                    }                


       }//皆輸入完成

        if ($comnote==''){$comnote='資料皆相符，請聯絡資料管理員。';}
      

        $this->comnote=$comnote;
        // dd('q');
    }



    public function render()
    {
        return view('livewire.fushan.seedling-compare');
    }
}
