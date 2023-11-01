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
 
class ss10mRecruitCheck
{
	public function check($data2, $entry){
		$data[0]=$data2;
		$pass='1';
		$datasavenote='';

        if ($entry == '1') {
            $table= new Ss10mTreeRecord1;
        } else {
            $table= new Ss10mTreeRecord2;
        }		


		for($i=0;$i<1;$i++){

			//是否重號
			
			$checkstemid=$table::where('stemid', 'like', $data[$i]['stemid'])->get();

			if (!$checkstemid->isEmpty()){  //重號
				$double="重號樹在 (".$checkstemid[0]['plot'].")(".$checkstemid[0]['sqx']." ,".$checkstemid[0]['sqy'].")";
				if ($checkstemid[0]['status']=='-9'){
					$datasavenote=$data[$i]['stemid'].' 已新增(重號)。['.$double."]";
					$pass="0";
					break;
				}
				if ($checkstemid[0]['show']=='1'){
						$datasavenote=$data[$i]['stemid'].' 重號，且為此次要調查的樹。 請確認號碼是否輸入錯誤。['.$double."]";
						$pass="0";break;
				} else {
					if ($data[$i]['tofix']!='1'){
						$datasavenote=$data[$i]['stemid'].' 重號，請確認是否漏資料。['.$double."]";
						$pass="0";break;
					}
				}
			} else { //沒有重號，但選漏資料
				if ($data[$i]['tofix']=='1'){
					$datasavenote=$data[$i]['stemid'].' 查無此樹，請確認是否漏資料，或洽管理員。';
					$pass="0";break;
				}
			}


//dbh欄位不得為0
			if ($data[$i]['dbh']=='0'){
				$datasavenote=$data[$i]['stemid'].' dbh/h高 需大於或等於 1。';
				$pass="0";break;
			}
        //4. code CIPR
            //自動轉為大寫
            $codea=[];
            // 4.1  			
            if ($data[$i]['code']!=''){
            	$codea=str_split($data[$i]['code']);

            //4.2 code只能是CIPR
                $codaarray=array("I","P","R");

                $arr3 = array_diff($codea, $codaarray);
                if (count($arr3) != 0) {
                    $datasavenote=$data[$i]['stemid']." code 只能是 I P R。";
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
            }

	// 8. 如果只有分支沒有主幹，不予新增
			if ($data[$i]['branch']!='0'){
				
				$stemid3=$table::where('tag', 'like', $data[$i]['tag'])->where('branch', 'like', '0')->where('plot', 'like', $data[$i]['plot'])->get();

				if ($stemid3->isEmpty()){
					$datasavenote=$data[$i]['stemid'].' 此分支沒有主幹。';
					$pass="0";break;
				} else {

					$ficus=['正榕', '雀榕'];

					$site1=$stemid3[0]['plot'].$stemid3[0]['sqx'].$stemid3[0]['sqy'];
					$site2=$data[$i]['plot'].$data[$i]['sqx'].$data[$i]['sqy'];
					if ($site1 != $site2){
						if (in_array($data[$i]['csp'], $ficus)){
							
							if (!in_array('R', $codea)){
								$datasavenote=$data[$i]['stemid'].' 分支與主幹不在同一小區，請在code註記R。';
								$pass="0";break;
							}
						} else {
							$datasavenote=$data[$i]['stemid'].' 分支與主幹需在同一小區。如為R，請將R分支之位置註記在note。';
							$pass="0";break;
						}
						
						
					
					}
				}
			}
	



        }


        $result=['pass'=>$pass, 'datasavenote'=>$datasavenote, 'data'=>$data2];

		return $result;

	}
}


