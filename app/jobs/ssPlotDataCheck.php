<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\Ss10mQuad2014;
use App\Models\Ss10mTree2014;
use App\Models\Ss10mTree2015;
use App\Models\Ss10mTreeEnviR1;
use App\Models\Ss10mTreeEnviR2;
use App\Models\Ss10mTreeCovR1;
use App\Models\Ss10mTreeCovR2;
use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;

use App\Models\Ss1haData2015; 
use App\Models\Ss1haRecord1;
use App\Models\Ss1haRecord2;
use App\Models\Ss1haEnviR1;
use App\Models\Ss1haEnviR2;


//壽山每木資料檢查
class SsPlotDataCheck
{
	public function check($data2, $plotType, $entry){
		$data[0]=$data2;
		$pass='1';
		$datasavenote='';
		
        for($i=0; $i<1;$i++){  //為了可以用break
            if ($data[$i]['date']=='0000-00-00' || $data[$i]['dbh']==''){
                $datasavenote=$data[$i]['stemid'].' 需有日期/dbh 資料';
                $pass='0';
                break;
            }
            //舊資料(census2015)  //確定dbh大小

            if ($plotType=='ss10m'){
                $table= new Ss10mTree2015;
                if ($entry==1){
                    $table2= new Ss10mTreeRecord1;
                } else {
                    $table2= new Ss10mTreeRecord2;
                }
            } else {
                $table= new Ss1haData2015;
                if ($entry==1){
                    $table2= new Ss1haRecord1;
                } else {
                    $table2= new Ss1haRecord2;
                }
            }
         
            $census2015=$table::where('stemid', 'like', $data[$i]['stemid'])->get()->toArray();

            //將code拆開

            $codea=str_split($data[$i]['code']);
            
            // $codea=$data[$i]['code'];
          //2. status 若不為空值，dbh須為0
            if ($data[$i]['status']!='' && $data[$i]['status']!='-9'){
                    if ($data[$i]['dbh']!='0'){
                        $datasavenote=$data[$i]['stemid'].' status不為空值，dbh/h高 需為0。';
                        $pass='0';
                        break;
                    }

                    if ($data[$i]['code']!='' && !in_array('F', $codea)){

                        $datasavenote=$data[$i]['stemid'].' status不為空值，code不得有值。';
                        $pass='0';
                        break;
                    }

                    if ($data[$i]['ill']!='0' && $data[$i]['leave']!='0' && $data[$i]['branch']!='0'){
                        $datasavenote=$data[$i]['stemid'].' status不為空值，ill, leave 應為 0。';
                        $pass='0';
                        break;
                    }
              

            }  else {
            //3. status 為空值，dbh不得為0


                // if ($data[$i]['ill']=='0'){
                //     $datasavenote=$data[$i]['stemid'].' status 為空值，ill 不得為0。';
                //     $pass='0';
                //     break;
                // }
                
                if ($data[$i]['dbh']=='0'){
                    $datasavenote=$data[$i]['stemid'].' status 為空值，dbh/h高 不得為0。';
                    $pass='0';
                    break;
                }

            //比較dbh大小
                if ($census2015!=[] ){
                    if ($data[$i]['branch']=='0'){
                        if ($data[$i]['dbh']<$census2015[0]['dbh']){
                            
                            if (in_array("C",$codea)){
                                if ($data[$i]['confirm']=='1'){
                                    $datasavenote=$data[$i]['stemid']." code 包含C，不需勾選縮水。";
                                    $pass='0';
                                    break;
                                }
                            } else {
                                if ($data[$i]['confirm']!='1'){
                                    $datasavenote=$data[$i]['stemid']." dbh 必須大於或等於上次調查，或勾選縮水，或是code要給C。";
                                    $pass='0';
                                    break;
                                }
                            }
                        } else {
                            if ($data[$i]['confirm']=='1'){
                                $datasavenote=$data[$i]['stemid']." dbh 大於上次調查，不應勾選縮水。";
                                $pass='0';
                                break;
                            }
                        }
                    } 
                }           
            }
        //4. code CIPRF
            //自動轉為大寫

            //4.1  若code包含C，則POM不得同於前次pom
            if ($data[$i]['code']!=''){


            //4.2 code只能是CIPR
                $codaarray=array("C","I","P","R","F");

                $arr3 = array_diff($codea, $codaarray);
                if (count($arr3) != 0) {
                    $datasavenote=$data[$i]['stemid']." code 只能是 C I P R F。";
                    $pass='0';
                    break;
                }


                //4.3 R只能出現在分支
                if (in_array("R",$codea)){  
                    if ($data[$i]['branch']=='0'){
                        $datasavenote=$data[$i]['stemid']." code R 只能記錄在分支。";
                        $pass='0';
                        break;
                    }
                }

                if (in_array("F",$codea)){ 
                    $ficus=['雀榕', '榕樹'];
                    if (!in_array($data[$i]['csp'], $ficus)){
                        $datasavenote=$data[$i]['stemid']." code F 只能記錄在 雀榕/榕樹 的氣生根。";
                        $pass='0';
                        break;
                    } else if ($data[$i]['branch']=='0'){
                        $datasavenote=$data[$i]['stemid']." code F 只能記錄在分支。";
                        $pass='0';
                        break;
                    }
                } 

                
                if (in_array("C", $codea)){
                    if ($data[$i]['status']!='-9'){
                        if ($data[$i]['pom']=='1.3'){ //第一次預設皆為1.3
                            $datasavenote=$data[$i]['stemid']." code包含C，則pom應與前次不同。";
                            $pass='0';
                            break;
                        }
                        if ($data[$i]['note']==''){
                            $datasavenote=$data[$i]['stemid']." code包含C，請在note欄位說明。";
                            $pass='0';
                            break;
                        }
                    } else {
                        $datasavenote=$data[$i]['stemid']." 為新增，code不得為C。";
                        $pass='0';
                        break;
                    }
                }
               
            }
        }


        $result=['pass'=>$pass, 'datasavenote'=>$datasavenote, 'data'=>$data[0]];

		return $result;

	}
}


