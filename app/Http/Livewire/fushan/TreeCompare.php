<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeEntrycom;

class TreeCompare extends Component
{

    public $comnote;

    public $entrylist=[];
    public $comparelist=[];
    public $qx;

    public function mount(){
// 確認是否輸入完資料
        $comnote='';
        $entrylist=[];
        $comparelist=[];

        $entrycom=FsTreeEntrycom::select('qx',  DB::raw('SUM(entry1) as sum1'), DB::raw('SUM(entry2) as sum2'))->groupBy('qx')->get()->toArray();
        $compareok=FsTreeEntrycom::select('qx',  DB::raw('SUM(compareOK) as sum1'))->groupBy('qx')->get()->toArray();

        foreach ($entrycom as $entry){
            if ($entry['sum1']==25 && $entry['sum2']==25){
                $entrylist[]=$entry['qx'];
            }
        }


        foreach ($compareok as $entry){
            if ($entry['sum1']==25){
                $comparelist[]=$entry['qx'];
            }
        }

        $this->entrylist=$entrylist;
        $this->comparelist=$comparelist;

    }

    public function compare(Request $request){
        $comnote='';

        $qx=$this->qx;
            // 比對小苗資料
        $pass='1';
        $tag1=array();
        $tag2=array();
        $record1=array();
        $record2=array();
                
            $s_record1=FsTreeRecord1::query()->where('qx', 'like', $qx)->get()->toArray();
            if (count($s_record1)>0){
                
                foreach ($s_record1 as $record){
                    $record['id']='';
                    $record['updated_at']='';
                    $record['update_id']='';
                    $stemid1[]=$record['stemid'];
                    $record1[$record['stemid']]=$record;
             }
            }

            $s_record2=FsTreeRecord2::query()->where('qx', 'like', $qx)->get()->toArray();
            if (count($s_record2)>0){
                   
                foreach ($s_record2 as $record){
                    $record['id']='';
                    $record['updated_at']='';
                    $record['update_id']='';
                    $stemid1[]=$record['stemid'];
                    $record2[$record['stemid']]=$record;
             }
            }
// dd($record1);

           
                //列出所有的tag
            $stemid2 = array_unique($stemid1);
            $stemid2 = array_diff($stemid2, array(""));

            sort($stemid2);    
 // dd($tag2);
            $comnote1=[];
                    // 依tag比對
            $mistake=[];
                    
            for ($i=0; $i<count($stemid2);$i++){
                $comnote2=[];
                if (isset($record1[$stemid2[$i]])){
                    //$comnote=$comnote.'step4<br>';
                    if (isset($record2[$stemid2[$i]])){  //12皆有
                        //$comnote=$comnote.'step5<br>';
                        $add2=[];
                        foreach ($record1[$stemid2[$i]] as $key => $value){
                        
                            if ($record2[$stemid2[$i]][$key]!=$value){
                                    // $add2=" [".$key.", (".$value."), (".$record2[$stemid2[$i]][$key].")]";  
                                    $add2[]=$key;             
                            }
                        }
                        if (count($add2)>0){
                            foreach ($add2 as $add21){
                                $comnote2['qy']=$record2[$stemid2[$i]]['qy'];
                                $comnote2['sqx']=$record2[$stemid2[$i]]['sqx'];
                                $comnote2['sqy']=$record2[$stemid2[$i]]['sqy'];
                                $comnote2['xy10']=$record2[$stemid2[$i]]['sqx'].$record2[$stemid2[$i]]['sqy'];
                                $comnote2['stemid']=$stemid2[$i];
                                if ($add21=='alternote'){$add21='特殊修改';}
                                if ($add21=='spcode'){continue;}
                                $comnote2['note']=$add21.' 資料不合';
                                $comnote1[]=$comnote2;
                                $mistake['key'][]=$add21;
                                $mistake['steimid'][]=$stemid2[$i];
                            }

                            
                            $pass='0';
                        }
                    } else {  //1有2沒有
                                $comnote2['qy']=$record1[$stemid2[$i]]['qy'];
                                $comnote2['sqx']=$record1[$stemid2[$i]]['sqx'];
                                $comnote2['sqy']=$record1[$stemid2[$i]]['sqy'];
                                $comnote2['xy10']=$record1[$stemid2[$i]]['sqx'].$record1[$stemid2[$i]]['sqy'];
                                $comnote2['stemid']=$stemid2[$i];
                                $comnote2['note']=' 第二次輸入缺資料';
                                $comnote1[]=$comnote2;
                                $mistake['key'][]='第二次輸入缺資料';
                                $mistake['steimid'][]=$stemid2[$i];
                        $pass='0';    
                    }

                } else {  //1沒有2有
                                $comnote2['qy']=$record2[$stemid2[$i]]['qy'];
                                $comnote2['sqx']=$record2[$stemid2[$i]]['sqx'];
                                $comnote2['sqy']=$record2[$stemid2[$i]]['sqy'];
                                $comnote2['xy10']=$record2[$stemid2[$i]]['sqx'].$record2[$stemid2[$i]]['sqy'];
                                $comnote2['stemid']=$stemid2[$i];
                                $comnote2['note']=' 第一次輸入缺資料';
                                $comnote1[]=$comnote2;
                                $mistake['key'][]='第一次輸入缺資料';
                                $mistake['steimid'][]=$stemid2[$i];
                        $pass='0';    
                }
                
                
            }
            // dd($comnote1);

            if ($pass=='0'){

               $mistake['steimid'] = array_unique($mistake['steimid']);

                $comnote='共有 '.count($comnote1).' 筆錯誤<br>';
                $comnote.='分別來自 '.count($mistake['steimid']).' 個枝幹<br>';

                $valueCounts = array_count_values($mistake['key']);

                foreach ($valueCounts as $key => $value){
                    $comnote.=$key.' 有'.$value."筆錯誤<br>";
                }
                $comnote.="<br>";

                usort($comnote1, function ($a, $b) {
                    // 首先按照 qy 進行排序
                    $qyComparison = $a['qy'] - $b['qy'];
                    
                    // 如果 qy 相同，則按照 xy10 進行排序
                    if ($qyComparison == 0) {
                        // 自定義 xy10 排序順序
                        $xy10Order = array("11","12","22","21","13","14","24","23","33","34","44","43","31","32","42","41");
                        $aXy10Index = array_search($a['xy10'], $xy10Order);
                        $bXy10Index = array_search($b['xy10'], $xy10Order);

                        return $aXy10Index - $bXy10Index;
                    }
                    
                    return $qyComparison;
                });

                foreach ($comnote1 as $note){
                    $comnote=$comnote.'每木'.$qx.'線資料比對:  ('.$qx.', '.$note['qy'].') ('.$note['sqx'].', '.$note['sqy'].') stemid '.$note['stemid']." ".$note['note']."<br>";
                }


                
            }


        // dd($comnote);


        if ($comnote==''){
            $comnote='資料皆相符。恭喜比對完成。';
            
            $uplist['compareOK']=$user;
            $uplist['compareOKdate']=date("Y-m-d H:i:s");

            FsTreeEntrycom::where('qx', 'like', $qx)->update($uplist);
        }
      

        $this->comnote=$comnote;
        $request->session()->put('comnote', $comnote);
        // dd('q');
    }


    public function render()
    {
        return view('livewire.fushan.tree-compare');
    }
}
