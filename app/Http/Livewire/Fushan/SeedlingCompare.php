<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
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


//小苗輸入完成後比對
    public $comnote;
    public $compare;
    public $cov1=[];
    public $cov2=[];

    public function mount(){
// 確認是否輸入完資料
        $comnote='';

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

       if ($comnote==''){ 
            $comnote='兩次輸入皆已完成，可進行比對。';

       }

       $this->compare=$comnote;

    }

//輸入完成比對
    public function compare(Request $request){
        $comnote='';


        //比對環境資料
            $cov1=FsSeedlingSlcov1::orderBy('trap', 'asc')->orderBy('plot', 'asc')->get()->toArray();
            for($i=0;$i<count($cov1);$i++){
                $cov11[$cov1[$i]['trap']][$cov1[$i]['plot']]=$cov1[$i];
            }

            $cov2=FsSeedlingSlcov2::orderBy('trap', 'asc')->orderBy('plot', 'asc')->get()->toArray();
            for($i=0;$i<count($cov2);$i++){
                $cov21[$cov2[$i]['trap']][$cov2[$i]['plot']]=$cov2[$i];
            }
            $comnote='';
// dd($cov11[106]);
            // $this->cov1=$cov11;
            // $this->cov2=$cov2;
            $pass1='1';
            $array=['year', 'month', 'date', 'cov', 'ht', 'canopy', 'note'];
            for ($j = 1; $j < 108; $j++) {
                if ($j==42) continue;
                for ($i = 1; $i < (count($cov11[$j])+1); $i++) {
                    
                    foreach ($cov11[$j][$i] as $key => $value) {
                        if (in_array($key, $array)){
                            if ($cov21[$j][$i][$key] != $value) {
                                $comnote.= '環境資料比對: 樣站 ' . $j . ' 有資料不合。<br>';
                                $pass1 = '0';
                                break 2; // 跳出兩層迴圈
                            }
                        }
                    }
                }
            }

            if ($pass1 == '0'){
                $comnote=$comnote."<br>";
            }

            // 比對小苗資料
            $pass2='1';
            $tag1=array();
            $tag2=array();
            $record1=array();
            $record2=array();

                
            $s_record1=FsSeedlingSlrecord1::get()->toArray();
            if (count($s_record1)>0){
                
                foreach ($s_record1 as $record){
                    $record['id']='';
                    $record['updated_at']='';
                    $record['updated_id']='';
                    $record['recruit']='';
                    $tag1[]=$record['tag'];
                    $record1[$record['tag']]=$record;
                }
            }

            $s_record2=FsSeedlingSlrecord2::get()->toArray();
            if (count($s_record2)>0){
                   
                foreach ($s_record2 as $record){
                    $record['id']='';
                    $record['updated_at']='';
                    $record['updated_id']='';
                    $record['recruit']='';
                    $tag1[]=$record['tag'];
                    $record2[$record['tag']]=$record;
                }
            }
// dd($record1);

           
                //列出所有的tag
            $tag2 = array_unique($tag1);
            $tag2 = array_diff($tag2, array(""));

            sort($tag2);    
 // dd($tag2);
            $comnote1=[];
                    //依tag比對

                    //$comnote=$comnote.$j.'step3<br>';
            for ($i=0; $i<count($tag2);$i++){
                $comnote2=[];
                if (isset($record1[$tag2[$i]])){
                    //$comnote=$comnote.'step4<br>';
                    if (isset($record2[$tag2[$i]])){  //12皆有
                        //$comnote=$comnote.'step5<br>';
                        $add2=[];
                        foreach ($record1[$tag2[$i]] as $key => $value){
                        
                            if ($record2[$tag2[$i]][$key]!=$value){
                                    // $add2=" [".$key.", (".$value."), (".$record2[$tag2[$i]][$key].")]";  
                                    $add2[]=$key;             
                            }
                        }
                        if (count($add2)>0){
                            foreach ($add2 as $add21){
                                $comnote2['trap']=intval($record1[$tag2[$i]]['trap']);
                                $comnote2['tag']=$tag2[$i];
                                $comnote2['note']=$add21.' 資料不合';
                                $comnote1[]=$comnote2;
                                // $comnote=$comnote.'小苗資料比對: 樣站 '.$record1[$tag2[$i]]['trap'].' No. '.$tag2[$i]. $add21.' 的資料不合。<br>';
                            }
                            
                            $pass2='0';
                        }
                    } else {  //1有2沒有
                                $comnote2['trap']=intval($record1[$tag2[$i]]['trap']);
                                $comnote2['tag']=$tag2[$i];
                                $comnote2['note']=' 第二次輸入缺資料';
                                $comnote1[]=$comnote2;
                        // $comnote=$comnote.'小苗資料比對: 樣站 '.$record1[$tag2[$i]]['trap'].' No. '.$tag2[$i].' 第二次輸入缺資料。<br>';
                        $pass2='0';    
                    }

                } else {  //1沒有2有
                                $comnote2['trap']=intval($record2[$tag2[$i]]['trap']);
                                $comnote2['tag']=$tag2[$i];
                                $comnote2['note']=' 第一次輸入缺資料';
                                $comnote1[]=$comnote2;
                        // $comnote=$comnote.'小苗資料比對: 樣站 '.$record2[$tag2[$i]]['trap'].' No. '.$tag2[$i].' 第一次輸入缺資料。<br>';
                        $pass2='0';    
                }
            }
            // dd($comnote1);

            if ($pass2=='0'){

                usort($comnote1, function ($a, $b) {
                    return $a['trap'] - $b['trap'];
                });

                foreach ($comnote1 as $note){
                    $comnote=$comnote.'小苗資料比對: 樣站 '.$note['trap']. ' tag '.$note['tag']." ".$note['note']."<br>";
                }


                
            }

// 比對撿到環資料
            $s_roll1=FsSeedlingSlroll1::orderBy('trap', 'asc')->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();

            if (!$s_roll1->isEmpty()){
                $s_roll1=$s_roll1->toArray();
                foreach($s_roll1 as $roll){
                    $roll['id']='';
                    $roll['updated_id']='';
                    $roll['updated_at']='';
                    $roll1[$roll['tag']]=$roll;
                    $tagroll1[]=$roll['tag'];
                }

            }
            $s_roll2=FsSeedlingSlroll2::orderBy('trap', 'asc')->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();

            if (!$s_roll2->isEmpty()){
                $s_roll2=$s_roll2->toArray();
                foreach($s_roll2 as $roll){
                    $roll['id']='';
                    $roll['updated_id']='';
                    $roll['updated_at']='';
                    $roll2[$roll['tag']]=$roll;
                    $tagroll1[]=$roll['tag'];
                }

            }
// dd($roll1);
            $tagroll2 = array_unique($tagroll1);
            $tagroll2 = array_diff($tagroll2, array(""));

            sort($tagroll2);
//                     //比對
            $pass3='1';

            for ($i=0; $i<count($tagroll2);$i++){
                if (isset($roll1[$tagroll2[$i]])){
                    if (isset($roll2[$tagroll2[$i]])){  //12都有
                        foreach ($roll1[$tagroll2[$i]] as $key => $value){
                            if ($roll2[$tagroll2[$i]][$key]!=$value){

                                $add3=" [".$key.", (".$value."), (".$roll2[$tagroll2[$i]][$key].")]";  
                                // $add3='['.$key.']';
                                $comnote=$comnote.'撿到環資料比對: 樣站 '.$roll1[$tagroll2[$i]]['trap'].' tag '.$roll1[$tagroll2[$i]]['tag'].' 的 '.$add3.' 資料不合。<br>';
                                $pass3='0';
                                break 2;

                            }
                        }

                    } else { //1有2沒有
                        $comnote.='撿到環資料比對: 第二次輸入缺少 tag :'.$roll1[$tagroll2[$i]]['tag']." 資料。<br>";
                        $pass3='0';
                    }
                } else { //1沒有2有
                    $comnote.='撿到環資料比對: 第一次輸入缺少 tag :'.$roll2[$tagroll2[$i]]['tag']." 資料。<br>";
                    $pass3='0';
                }
            }



        if ($comnote==''){$comnote='資料皆相符，請聯絡資料管理員。';}
      

        $this->comnote=$comnote;
        $request->session()->put('comnote', $comnote);
        // dd('q');
    }



    public function render()
    {
        return view('livewire.fushan.seedling-compare');
    }
}
