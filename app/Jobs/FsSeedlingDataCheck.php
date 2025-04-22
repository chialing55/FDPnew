<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingBase;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;

//福山小苗資料檢查
//可依所需調整

class FsSeedlingDataCheck
{
	public function check($data2, $table){
		$data[0]=$data2;
		$pass='1';
		$datasavenote='';
		for($i=0;$i<1;$i++){

            if ($data[$i]['date']=='0000-00-00' || $data[$i]['ht']=='' || $data[$i]['leafno']==''){
                $datasavenote=$data[$i]['tag'].' 需有日期/長度/葉片數 資料';
                $pass='0';
                break;
            }

            // if (!isset($data[$i]['note'])){$data[$i]['note']='';}

            if ($data[$i]['sprout']=='TRUE'){///sprout

                $sprout_tag=explode('.',trim($data[$i]['tag']));

                $slrecord=$table::where('mtag', 'like', $sprout_tag[0])->where('sprout', 'like', 'FALSE')->get();

                if (!$slrecord->isEmpty()){

                    if ($data[$i]['ht']=='-4' && $slrecord[0]['ht']!='-4'){
                        $datasavenote=$data[$i]['tag']." 植株存活但萌櫱苗死亡，萌櫱苗記為 -7, status 為 A";
                        $pass='0';
                        break;
                    }

                    if ($data[$i]['ht']=='-6' && $slrecord[0]['ht']!='-6'){
                        $datasavenote=$data[$i]['tag']." 植株存活但萌櫱苗消失，萌櫱苗記為 -7, status 為 A";
                        $pass='0';
                        break;
                    }

                    if ($data[$i]['status']!=$slrecord[0]['status']){
                        $datasavenote=$data[$i]['tag']." 萌櫱苗的 status 需與主幹相同 (若該植株尚有分支存活，status 皆為 A)";
                        $pass='0';
                        break;
                    }

                    if ($data[$i]['csp']!=$slrecord[0]['csp']){
                        $datasavenote=$data[$i]['tag']." 萌櫱苗的 csp 需與主幹相同 ";
                        $pass='0';
                        break;
                    }

                    if ($data[$i]['trap']!=$slrecord[0]['trap'] || $data[$i]['plot']!=$slrecord[0]['plot']){
                        $datasavenote=$data[$i]['tag']." 萌櫱苗的樣區位置需與主幹相同 ";
                        $pass='0';
                        break;
                    }
                }
            }

//長度
            // $data[$i]['ht']=intval($data[$i]['ht']);
            // $data[$i]['cotno']=intval($data[$i]['cotno']);
            // $data[$i]['leafno']=intval($data[$i]['leafno']);

            if ($data[$i]['ht']<-1){
                // break;

                if ($data[$i]['cotno']!=$data[$i]['ht'] || $data[$i]['leafno'] !=$data[$i]['ht'] || $data[$i]['leafno'] !=$data[$i]['cotno'] ){
                    $datasavenote=$data[$i]['tag'].' 長度 <-1 時，子葉數、真葉數需與長度相同';
                    $pass='0';
                    break;

                } else if ($data[$i]['ht']=='-1' && $data[$i]['status']!='A'){
                    $datasavenote=$data[$i]['tag']." 長度 = -1，狀態欄需為 A";
                    $pass='0';
                    break;
                // } else if ($data[$i]['ht']=='-2' && $data[$i]['status']!='L'){
                //     $datasavenote=$data[$i]['tag']." 長度 = -2，狀態欄需為 L";
                //      ;
                //     break;
                } else if ($data[$i]['ht']=='-4'){
                    if ($data[$i]['status']!='G' && $data[$i]['status']!='D'){
                        $datasavenote=$data[$i]['tag']." 長度 = -4，狀態欄需為 G or D";
                        $pass='0';
                        break;
                    }
                } else if ($data[$i]['ht']=='-6' && $data[$i]['status']!='N'){
                        $datasavenote=$data[$i]['tag']." 長度 = -6，狀態欄需為 N";
                        $pass='0';
                        break;
                } else if ($data[$i]['ht']=='-7' && $data[$i]['status']!='A'){
                        $datasavenote=$data[$i]['tag']." 長度 = -7，狀態欄需為 A";
                        $pass='0';
                        break;
                }
                
            } else if ($data[$i]['ht']==0){
                $datasavenote=$data[$i]['tag']." 長度 不得為 0";
                $pass='0';
                break;
            } else  if ($data[$i]['ht']>'0' && $data[$i]['status']!='A'){
                $datasavenote=$data[$i]['tag']." 長度 > 0，狀態欄需為 A";
                $pass='0';
                break;
            }

// //子葉數
            if ($data[$i]['cotno']>2){
                $datasavenote=$data[$i]['tag']." 子葉數不得 > 2";
                $pass='0';
                break;
            } 

            $slrecord=FsSeedlingData::where('tag', 'like', $data[$i]['tag'])->orderBy('id', 'DESC')->get();
            if (!$slrecord->isEmpty()){
                if ($slrecord[0]['cotno']>=0 && $data[$i]['cotno']> $slrecord[0]['cotno']){
                    $datasavenote=$data[$i]['tag']." 子葉數不得增加，如需修改增加和舊資料，請利用特殊修改";
                    $pass='0';
                    break;
                }
            }

            if ($data[$i]['cotno']==Null){
                $data[$i]['cotno']='0';
            }

//位置資料   
            if (isset($data[$i]['x'])){
                if ($data[$i]['x'] >100 || $data[$i]['y'] >100){
                    $datasavenote=$data[$i]['tag']." 座標不得大於100";
                    $pass='0';
                    break;
                }
            }



        }


        $result=['pass'=>$pass, 'datasavenote'=>$datasavenote, 'data'=>$data[0]];

		return $result;

	}
}


