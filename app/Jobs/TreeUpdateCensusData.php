<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;



use App\Models\FsTreeFixlog;
use App\Models\FsTreeCensus5;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeCensus2;
use App\Models\FsTreeCensus1;
use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;


use App\Models\Ss1haData2015;
use App\Models\Ss1haData2024;
use App\Models\Ss1haBase2015;
use App\Models\Ss1haBase2024;
use App\Models\Ss1haBaseR2024;
use App\Models\SsFixlog;

// use App\Models\Ss10mBase2015;
// use App\Models\Ss10mBase2024;
// use App\Models\Ss10mBaseR2024;
//後端資料表更新
//每一個要更新的資料表會進這邊更新
class TreeUpdateCensusData
{
	public function up($plotType, $i, $lastcensus, $ndata, $odata, $table, $data_all, $ostemid){

        if ($plotType=='fstree'){
            $tablebase=new FsTreeBase;
            $tablebaser=new FsTreeBaseR;
            $tablefixlog=new FsTreeFixlog;
            $censusSheet='census'.$i;
            $baseSheet='base';
            $baseRSheet='base_r';
        } else if ($plotType=='ss1ha'){
            $tablebase = new Ss1haBase2024;
            $tablebaser = new Ss1haBaseR2024;
            $tablefixlog = new SsFixlog;
            if ($i==1){
                $censusSheet='1ha_data_2015';
            } else if ($i==2){
                $censusSheet='1ha_data_2024';
            }
            $baseSheet='1ha_base_2024';
            $baseRSheet='1ha_base_r_2024';
        }

        $user=$data_all['user'];
        $from=$data_all['from'];

        $base=$data_all['data1'][0];  
        $otag = explode('.', $ostemid)[0] ?? $ostemid;;

                $updatedes=[];
                $census_uplist=[];
                $keylist=[];

                foreach ($ndata as $key=>$value){
                    if (is_null($value)) {
                        $ndata[$key] = '';
                    }
                }
            $datasavenote='';

                if (count($odata)>0){
                    $exarray=['code2','status2','updated_id', 'updated_at', 'deleted_at'];
                    foreach ($odata as $key =>$value){
                        
                        if (!in_array($key, $exarray)){
                            if (!isset($ndata[$key])){
                                $keylist[]=$key;
                            } else
                            if ($value != $ndata[$key]){
                                $census_uplist[$key]=$ndata[$key];
                                $updatedes[$key]=$value."=>".$ndata[$key];
                            }
                        }                    
                    }
                 }

                $fixlog=[];
                if ($census_uplist!=[]){
                    $census_uplist['updated_id']=$user;
                    $table::where('stemid', 'like', $ostemid)->update($census_uplist);
                    $fixlog['id']='0';
                    $fixlog['from']=$from;
                    $fixlog['type']='update';
                    $fixlog['sheet']=$censusSheet;
                    $fixlog['qx']=$base['qx'];
                    $fixlog['stemid']=$ostemid;
                    $fixlog['descript']=json_encode($updatedes, JSON_UNESCAPED_UNICODE);
                    $fixlog['updated_id']=$user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                    $tablefixlog::insert($fixlog);
                    $datasavenote='已更新資料';
                }

// //原本沒有R，改成R
                 $codetemp=[];
                 $baser_insert=[];
                if($i==$lastcensus){
                    $fixlog=[];
                    if ($odata['code']!=$ndata['code']){
                        $codetemp=[$odata['code'], $ndata['code']];
                        if (stripos($odata['code'], 'R') !== false && stripos($ndata['code'], 'R') == false){
                            //原有R，後來刪除
                            $tablebaser::where('stemid', 'like', $ostemid)->delete();

                            $fixlog['id']='0';
                            $fixlog['from']=$from;
                            $fixlog['type']='delete';
                            $fixlog['sheet']=$baseRSheet;
                            $fixlog['qx']=$base['qx'];
                            $fixlog['stemid']=$ostemid;
                            $fixlog['descript']='刪除此編號base_r資料';
                            $fixlog['updated_id']=$user;
                            $fixlog['updated_at']=date("Y-m-d H:i:s");
                            $tablefixlog::insert($fixlog);

                        } else if (stripos($odata['code'], 'R') == false && stripos($ndata['code'], 'R') !== false){
                        //     //原沒有R，後來新增

                            $baserO=$tablebaser::where('stemid', 'like', $ostemid)->get()->toArray();
                            if (count($baserO)>0){
                                $tablebaser::where('stemid', 'like', $ostemid)->update(['deleted_at'=>'']);
                                $fixlog['id']='0';
                                $fixlog['from']=$from;
                                $fixlog['type']='update';
                                $fixlog['sheet']=$baseRSheet;
                                $fixlog['qx']=$base['qx'];
                                $fixlog['stemid']=$ostemid;
                                $fixlog['descript']='恢復此編號base_r資料';
                                $fixlog['updated_id']=$user;
                                $fixlog['updated_at']=date("Y-m-d H:i:s");
                                $tablefixlog::insert($fixlog);
                            } else {

                                $baseRdata=$tablebase::where('tag', 'like', $otag)->first()->toArray();
                                $baser_insert=[];
                                $exarray=['updated_id', 'updated_at', 'deleted_at'];

                                foreach($baseRdata as $key=>$value){
                                    if (!in_array($key, $exarray)){
                                        $baser_insert[$key]=$value;
                                    }
                                }
                                $baser_insert['qudx']=0;
                                $baser_insert['qudy']=0;
                                $baser_insert['plotx']=0;
                                $baser_insert['ploty']=0;
                                $baser_insert['stemid']=$ostemid;
                                $baser_insert['updated_id']=$user;
                                $baser_insert['updated_at']=date("Y-m-d H:i:s");
                                $baser_insert['deleted_at']='';

                                $tablebaser::insert($baser_insert);

                                $fixlog['id']='0';
                                $fixlog['from']=$from;
                                $fixlog['type']='insert';
                                $fixlog['sheet']=$baseRSheet;
                                $fixlog['qx']=$base['qx'];
                                $fixlog['stemid']=$ostemid;
                                $fixlog['descript']='新增base_r';
                                $fixlog['updated_id']=$user;
                                $fixlog['updated_at']=date("Y-m-d H:i:s");
                                $tablefixlog::insert($fixlog);
                            }
                        }
                    }
                }

                if ($plotType =='ss1ha' || $plotType =='ss10m'){
                //處理F
                    if($i==$lastcensus){
                        $fixlog=[];
                        if ($odata['code']!=$ndata['code']){
                            $codetemp=[$odata['code'], $ndata['code']];
                            if (stripos($odata['code'], 'F') !== false && stripos($ndata['code'], 'F') == false){
                                //原有F，後來刪除
                                $tablebaser::where('stemid', 'like', $ostemid)->delete();

                                $fixlog['id']='0';
                                $fixlog['from']=$from;
                                $fixlog['type']='delete';
                                $fixlog['sheet']=$baseRSheet;
                                $fixlog['qx']=$base['qx'];
                                $fixlog['stemid']=$ostemid;
                                $fixlog['descript']='刪除此編號base_r資料';
                                $fixlog['updated_id']=$user;
                                $fixlog['updated_at']=date("Y-m-d H:i:s");
                                $tablefixlog::insert($fixlog);

                            }

                            else if (stripos($odata['code'], 'F') == false && stripos($ndata['code'], 'F') !== false){
                            //     //原沒有F，後來新增

                                $baserO=$tablebaser::where('stemid', 'like', $ostemid)->get()->toArray();
                                if (count($baserO)>0){
                                    $tablebaser::where('stemid', 'like', $ostemid)->update(['deleted_at'=>'']);
                                    $fixlog['id']='0';
                                    $fixlog['from']=$from;
                                    $fixlog['type']='update';
                                    $fixlog['sheet']=$baseRSheet;
                                    $fixlog['qx']=$base['qx'];
                                    $fixlog['stemid']=$ostemid;
                                    $fixlog['descript']='恢復此編號base_r資料';
                                    $fixlog['updated_id']=$user;
                                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                                    $tablefixlog::insert($fixlog);
                                } else {

 
                                    $baseRdata=$tablebase::where('tag', 'like', $otag)->first()->toArray();
                                    $baser_insert=[];
                                    $exarray=['updated_id', 'updated_at', 'deleted_at'];

                                    foreach($baseRdata as $key=>$value){
                                        if (!in_array($key, $exarray)){
                                            $baser_insert[$key]=$value;
                                        }
                                    }
                                    $baser_insert['qudx']=0;
                                    $baser_insert['qudy']=0;
                                    $baser_insert['plotx']=0;
                                    $baser_insert['ploty']=0;                                    
                                    $baser_insert['stemid']=$ostemid;
                                    $baser_insert['updated_id']=$user;
                                    $baser_insert['updated_at']=date("Y-m-d H:i:s");
                                    $baser_insert['deleted_at']='';

                                    $tablebaser::insert($baser_insert);

                                    $fixlog['id']='0';
                                    $fixlog['from']=$from;
                                    $fixlog['type']='insert';
                                    $fixlog['sheet']=$baseRSheet;
                                    $fixlog['qx']=$base['qx'];
                                    $fixlog['stemid']=$ostemid;
                                    $fixlog['descript']='新增base_r';
                                    $fixlog['updated_id']=$user;
                                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                                    $tablefixlog::insert($fixlog);
                                }
                            }
                        }
                    }
                }



        $result=['datasavenote'=>$datasavenote, 'keylist'=>$keylist];

		return $result;

    }
}

