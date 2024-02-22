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

class FsSeedlingRecruitCheck
{
	public function check($data2, $entry, $q){
		$recruit[0]=$data2;
		$pass='1';
		$recruitsavenote='';

        if ($entry == '1') {
            $table= new FsSeedlingSlrecord1;
        } else {
            $table= new FsSeedlingSlrecord2;
        }


		for($i=0;$i<1;$i++){

//重號檢查
                $seedling=FsSeedlingData::where('tag', 'like', $recruit[$i]['tag'])->get();
                if (!$seedling->isEmpty()){
                    $recruitsavenote = $recruitsavenote."<br>第".($q+1).'筆資料 重號';
                    $pass="0";
                    break;
                } else {
                    
                    $seedling2=$table::where('tag', 'like', $recruit[$i]['tag'])->get();

                    if (!$seedling2->isEmpty()){
                        $recruitsavenote = $recruitsavenote."<br>第".($q+1).'筆資料 重號或已輸入';
                        $pass="0";
                        break;
                    }                    
                }
                $mtag=explode('.', $recruit[$i]['tag']);
                $recruit[$i]['mtag']=$mtag[0];

                if (isset($mtag[1])){  //萌櫱
                    if ($recruit[$i]['sprout']=='FALSE'){
                        $recruitsavenote = $recruitsavenote."<br>第".($q+1).'筆資料 萌櫱狀態錯誤，請確認';
                        $pass="0";
                        break;
                    }

                    if ($recruit[$i]['cotno']>0){
                        $recruitsavenote = $recruitsavenote."<br>第".($q+1).'筆資料 萌櫱苗不會有子葉，請確認';
                        $pass="0";
                        break;
                    }
                }


                if ($recruit[$i]['ht']==0 ){
                    $recruitsavenote= $recruitsavenote."<br>第".($q+1).'筆資料 長度不得為 0';
                    $pass="0";
                    break;
                } else if ($recruit[$i]['ht']<0){
                    if ($recruit[$i]['cotno']!=$recruit[$i]['ht'] || $recruit[$i]['leafno'] !=$recruit[$i]['ht']){
                        $recruitsavenote= $recruitsavenote."<br>第".($q+1).'筆資料 長度 < 0，子葉數與葉片數需與長度相同';
                        $pass="0";
                        break;
                    }
                }

                 
                if ($recruit[$i]['cotno']>2){
                    $recruitsavenote= $recruitsavenote."<br>第".($q+1).'筆資料 子葉數不得 > 2';
                    $pass="0";
                    break;
                }

                if ($recruit[$i]['sprout']=='TRUE'){
                    $recruit[$i]['x']='0';
                    $recruit[$i]['y']='0';

                    //找出是否有主幹資料

                    $sprout=$table::where('mtag', 'like', $recruit[$i]['mtag'])->where('sprout', 'like', 'FALSE')->get();

                    if (!$sprout->isEmpty()){
                        if ($sprout[0]['status']!='A'){
                            // $recruit[$i]['alternote']='主幹狀態不為A，請確認。';
                            $recruitsavenote=$recruitsavenote."<br>第".($q+1)."筆資料 主幹狀態不為 A，請確認。<br>";
                            $pass="0";
                            break;

                        }

                        if ($recruit[$i]['csp']!=$sprout[0]['csp']){
                            $recruitsavenote=$recruitsavenote."<br>第".($q+1)."筆資料 萌櫱苗與主幹種類不同，請確認。<br>";
                            $pass="0";
                            break;
                        }

                        if ($recruit[$i]['trap']!=$sprout[0]['trap'] || $recruit[$i]['plot']!=$sprout[0]['plot']){
                            $recruitsavenote=$recruitsavenote."<br>第".($q+1)."筆資料 萌櫱苗與主幹的樣區位置不同，請確認。<br>";
                            $pass="0";
                            break; 
                        }

                    } else {
                        // $recruit[$i]['alternote']='未有主幹資料，請確認。';
                        $recruitsavenote=$recruitsavenote."<br>第".($q+1)."筆資料 未有主幹資料，請確認。或請管理員加入資料。<br>";
                        $pass="0";
                        break;
                    }

                }

                if ($recruit[$i]['x']==''){
                    if ($recruit[$i]['sprout']!='TRUE'){
                        $recruitsavenote= $recruitsavenote."<br>第".($q+1).'筆資料 需有座標';
                        $pass="0";
                        break;
                    } else {
                        $recruit[$i]['x']='0';
                    }
                    
                } else if  ($recruit[$i]['x']>100 || $recruit[$i]['x']<0) {
                    $recruitsavenote= $recruitsavenote."<br>第".($q+1).'筆資料 座標不得 > 100 或 < 0';
                    $pass="0";
                    break;
                }
                if ($recruit[$i]['y']==''){
                    if ($recruit[$i]['sprout']!='TRUE'){
                        $recruitsavenote= $recruitsavenote."<br>第".($q+1).'筆資料 需有座標';
                        $pass="0";
                        break;
                    } else {
                        $recruit[$i]['y']='0';
                    }
                } else if  ($recruit[$i]['y']>100 || $recruit[$i]['y']<0) {
                    $recruitsavenote= $recruitsavenote."<br>第".($q+1).'筆資料 座標不得 > 100 或 < 0';
                    $pass="0";
                    break;
                }



        }


        $result=['pass'=>$pass, 'datasavenote'=>$recruitsavenote, 'data'=>$recruit];

		return $result;

	}
}


