<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;


use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeEntrycom;

use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;
use App\Models\Ss1haRecord1;
use App\Models\Ss1haRecord2;
use App\Models\SsEntrycom;

class TreeCompareCheck
{
	public function check(Request $request, $record1, $record2, $allStemid, $plotSize, $plotType){

            $comnote='';
            $comnote1=[];
                    // 依tag比對
            $mistake=[];
            $arrayExculd=['update_id', 'updated_at'];
            foreach ($allStemid as $stemid) {
                $comnote2=[];
                if (isset($record1[$stemid])){
                    //$comnote=$comnote.'step4<br>';
                    if (isset($record2[$stemid])){  //12皆有
                        //$comnote=$comnote.'step5<br>';

                        $add2=[];
                        foreach ($record1[$stemid] as $key => $value){
                            if (!in_array($key, $arrayExculd) && $record2[$stemid][$key]!=$value){
                                    // $add2=" [".$key.", (".$value."), (".$record2[$stemid][$key].")]";  
                                    $add2[]=$key;             
                            }
                        }
                        // dd($add2);
                        if ($add2!=[]){
                       // dd($add2);
                            if (isset($record2[$stemid]['qx'])){
                                $comnote2['plot']=$record2[$stemid]['qx'].', '.$record2[$stemid]['qy'];
                            } else {
                                $comnote2['plot']=$record2[$stemid]['plot'];
                            }
                            $comnote2['sqx']=$record2[$stemid]['sqx'];
                            $comnote2['sqy']=$record2[$stemid]['sqy'];
                            $comnote2['xy10']=$record2[$stemid]['sqx'].$record2[$stemid]['sqy'];
                            $comnote2['stemid']=$stemid;
                            $comnote2['note']='';

                            foreach ($add2 as $add21){
                                if ($add21=='alternote'){$add21='特殊修改';}
                                if ($add21=='spcode'){$add21='csp';}
                                $comnote2['note'].='['.$add21.']';
                                $mistake[]=$add21;
                            }
                                $comnote2['note'].=' 資料不和';
                                $comnote1[]=$comnote2;
                            // dd($mistake['steimid']);
                        }

                         // dd($pass);
                    } else {  //1有2沒有
                                if (isset($record1[$stemid]['qx'])){
                                    $comnote2['plot']=$record1[$stemid]['qx'].', '.$record1[$stemid]['qy'];
                                } else {
                                    $comnote2['plot']=$record1[$stemid]['plot'];
                                }
                                $comnote2['sqx']=$record1[$stemid]['sqx'];
                                $comnote2['sqy']=$record1[$stemid]['sqy'];
                                $comnote2['xy10']=$record1[$stemid]['sqx'].$record1[$stemid]['sqy'];
                                $comnote2['stemid']=$stemid;
                                $comnote2['note']=' 第二次輸入缺資料';
                                $comnote1[]=$comnote2;
                                $mistake[]='第二次輸入缺資料';

                    }

                } else {  //1沒有2有
                                if (isset($record2[$stemid]['qx'])){
                                    $comnote2['plot']=$record2[$stemid]['qx'].', '.$record2[$stemid]['qy'];
                                } else {
                                    $comnote2['plot']=$record2[$stemid]['plot'];
                                }
                                $comnote2['sqx']=$record2[$stemid]['sqx'];
                                $comnote2['sqy']=$record2[$stemid]['sqy'];
                                $comnote2['xy10']=$record2[$stemid]['sqx'].$record2[$stemid]['sqy'];
                                $comnote2['stemid']=$stemid;
                                $comnote2['note']=' 第一次輸入缺資料';
                                $comnote1[]=$comnote2;
                                $mistake[]='第一次輸入缺資料';

                }
            }

            // dd($pass);

            if ($comnote1 !=[]){

                $comnote='共有 '.count($mistake).' 筆錯誤<br>';

                if ($plotType=='ss10mCov'){
                    $comnote.='分別來自 '.count($comnote1).' 筆資料<br>';
                } else {
                    $comnote.='分別來自 '.count($comnote1).' 個枝幹<br>';
                }
                

                $valueCounts = array_count_values($mistake);

                foreach ($valueCounts as $key => $value){
                    $comnote.=$key.' 有'.$value."筆錯誤<br>";
                }
                $comnote.="<br>";

                usort($comnote1, function ($a, $b) use ($plotSize) {
                    // 首先按照 qy 進行排序
 
                    $qyComparison = strcmp($a['plot'], $b['plot']);  //依字母排序
                    
                    // 如果 qy 相同，則按照 xy10 進行排序
                    if ($qyComparison == 0) {
                        // 自定義 xy10 排序順序
                        if ($plotSize=='20'){
                            $xy10Order = array("11","12","22","21","13","14","24","23","33","34","44","43","31","32","42","41");
                        } else {
                            $xy10Order = array("11","12","22","21");
                        }
                        
                        $aXy10Index = array_search($a['xy10'], $xy10Order);
                        $bXy10Index = array_search($b['xy10'], $xy10Order);

                        return $aXy10Index - $bXy10Index;
                    }
                    
                    return $qyComparison;
                });

                
                    foreach ($comnote1 as $note){

                        if ($plotType=='fsTree'){
                            $noteTitle='每木'.$record1[$allStemid[0]]['qx'].'線';
                            
                        } else if ($plotType=='ss1ha' || $plotType=='ss10m'){
                            $noteTitle='每木';
                        } else if ($plotType=='ss10mCov'){
                            $noteTitle='地被';
                        }

                        $comnote=$comnote.$noteTitle.'資料比對:  ('.$note['plot'].') ('.$note['sqx'].', '.$note['sqy'].') stemid '.$note['stemid']." ".$note['note']."<br>";
                    }
            } 

		return $comnote;

    }
}

