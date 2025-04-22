<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;


use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;
use App\Models\FsTreeFixlog;
use App\Models\FsTreeCensus5;

use App\Models\Ss1haData2024;
use App\Models\Ss1haBase2024;
use App\Models\Ss1haBaseR2024;
use App\Models\SsFixlog;

// use App\Models\Ss10mBase2015;
// use App\Models\Ss10mBase2024;
// use App\Models\Ss10mBaseR2024;

class TreeUpdateBase
{
	public function up($plotType, $data_all, $splist){

        $datasavenote='';
        $result=[];

        if ($plotType=='fstree'){
            $tablebase=new FsTreeBase;
            $tablebaser=new FsTreeBaseR;
            $tablecensus=new FsTreeCensus5;
            $tablefixlog=new FsTreeFixlog;
            $baseSheet='base';
            $baseRSheet='base_r';
        } else if ($plotType=='ss1ha'){
            $tablebase = new Ss1haBase2024;
            $tablebaser = new Ss1haBaseR2024;
            $tablecensus = new Ss1haData2024;
            $tablefixlog = new SsFixlog;
            $baseSheet='1ha_base_2024';
            $baseRSheet='1ha_base_r_2024';
        }

        $user=$data_all['user'];
        $from=$data_all['from'];

        $base=$data_all['data1'][0];  
        $base['spcode'] = array_search($base['csp'], $splist); 
        //原有編號       
        $stemidtemp=explode('.', $base['stemid']);
        $otag=$stemidtemp[0];
        $ostemid=$base['stemid'];
        if (count($stemidtemp)>1){
            $obranch=$stemidtemp[1];
        } else {
            $obranch='0';
            $ostemid=$ostemid.".0";
        }
        //新編號 
        $newstemid=$base['tag'].".".$base['branch'];
        $baseWay='0';
        $pass='1';

        if ($newstemid!=$ostemid){
            $check=$tablecensus::where('stemid', 'like', $newstemid)->count();
            if ($check>0){
                $datasavenote='重號。不予更新。';
                $pass='0';
            } else {
                 if ($base['branch']=='0'){ //變成主幹
                    if ($obranch!='0'){ //原本是分支
                        //分支變主幹  //新增base
                        $baseWay='1';
                    } else {
                        $baseWay='0';  //換號
                    }
                } else{  //變成別人的分支  //先確定是否有主幹
                    $baseWay='2';  //將該編號軟刪除
                    $check2=$tablebase::where('tag', 'like', $base['tag'])->count();
                    if ($check2==0){
                        $datasavenote='沒有主幹，不予更新。';
                        $pass='0';
                    }
                }
            }
        }

        $fixlog=[];
        $thisstemid='';

        if ($pass=='1'){
 
       //沒有重號再改$thisstemid
            if ($from=='alternote' && $newstemid!=$ostemid){
                $thisstemid=$newstemid;
            }  

        if ($base['r']=='y'){
            $obase=$tablebaser::where('stemid','like',$base['stemid'])->first()->toArray();
        } else {
            $obase=$tablebase::where('tag','like',$otag)->first()->toArray();
        }


        $updatedes=[];
        $basetable='';
        $base_uplist=[];
        $fixlog=[];
        //如果改號碼，多是換號，直接改掉base的資料
            if ($baseWay=='0'){  //修改base
                //base
                    switch ($base['sqx']) {
                        case '1':
                        case '2':
                            $base['subqx'] = '1';
                            break;
                        case '3':
                        case '4':
                            $base['subqx'] = '2';
                            break;
                        default:
                            $base['subqx']; // 保留原始值
                            break;
                    }

                    switch ($base['sqy']) {
                        case '1':
                        case '2':
                            $base['subqy'] = '1';
                            break;
                        case '3':
                        case '4':
                            $base['subqy'] = '2';
                            break;
                        default:
                            $base['subqy']; // 保留原始值
                            break;
                    }


                //修改base_r
                if ($base['r']=='y'){
                    $basetable='r';
                }

                $exarray=['updated_id', 'updated_at', 'deleted_at'];
                foreach($obase as $key=>$value){
                    if (!in_array($key, $exarray)){
                        if ($value != $base[$key]){
                            $base_uplist[$key]=$base[$key];
                            $updatedes[$key]=$value."=>".$base[$key];
                        }
                    }
                }
               $fixlog['type']='update';
               $fixlog['stemid']=$otag;
            } else if ($baseWay=='1'){  //新增
                $exarray=['updated_id', 'updated_at', 'deleted_at'];
                foreach($obase as $key=>$value){
                    if (!in_array($key, $exarray)){
                        if(isset($base[$key])){
                            $base_uplist[$key]=$base[$key];
                            $updatedes[$key]= $base[$key];
                        } else {
                            $base_uplist[$key]='0';
                        }
                    }
                }
                $fixlog['type']='insert';
                $fixlog['stemid']=$base['tag'];
            } else if ($baseWay=='2'){  //主幹變別人分支，該號碼需被軟刪除  //但分支變成別人的分支不用
                if ($obranch=='0'){
                    $base_uplist['deleted_at']=date("Y-m-d H:i:s");
                    $updatedes['deleted_at']=$base_uplist['deleted_at'];
                    $fixlog['type']='delete';
                    $fixlog['stemid']=$otag;
                }
            }

            if ($base_uplist!=[]){
                $base_uplist['updated_id']=$user;
                if ($baseWay=='0'){
                    if ($basetable==''){
                        $tablebase::where('tag', 'like', $otag)->update($base_uplist);
                        $fixlog['sheet']=$baseSheet;
                    } else {
                        $tablebaser::where('stemid', 'like', $base['stemid'])->update($base_uplist);
                        $fixlog['sheet']=$baseRSheet;
                    }
                    
                } else if ($baseWay=='1'){ 
                    $base_uplist['updated_at']=date("Y-m-d H:i:s");
                    $base_uplist['deleted_at']='';
                    $tablebase::insert($base_uplist);
                    $fixlog['sheet']=$baseSheet;
                } else if ($baseWay=='2'){ 
                    $base_uplist['updated_at']=date("Y-m-d H:i:s");
                    $tablebase::where('tag', 'like', $otag)->update($base_uplist);
                    $fixlog['sheet']=$baseSheet;
                }


                $fixlog['id']='0';
                $fixlog['from']=$from;
                $fixlog['qx']=$base['qx'];
                
                $fixlog['descript']=json_encode($updatedes, JSON_UNESCAPED_UNICODE);
                $fixlog['updated_id']=$user;
                $fixlog['updated_at']=date("Y-m-d H:i:s");
                $tablefixlog::insert($fixlog);


                $datasavenote='已更新資料';

            }
        }

        $result=['datasavenote'=>$datasavenote, 'thisstemid'=>$thisstemid, 'ostemid'=>$ostemid, 'newstemid' => $newstemid, 'pass'=>$pass];

		return $result;

    }
}

