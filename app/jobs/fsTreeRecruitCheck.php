<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus5;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeBase;

//每木新增樹檢查 
class FsTreeRecruitCheck
{
	public function check($data2, $entry){
		$data[0]=$data2;
		$pass='1';
		$datasavenote='';

        if ($entry == '1') {
            $table= new FsTreeRecord1;
        } else if ($entry == '2') {
            $table= new FsTreeRecord2;
        } else if ($entry == '3') {
            $table= new FsTreeCensus5;
        }	


		for($i=0;$i<1;$i++){

			//是否重號
			if ($entry == '3') {
			    $data[$i]['tofix']='0';
			}
			$checkstemid=$table::where('stemid', 'like', $data[$i]['stemid'])->get();

			if (!$checkstemid->isEmpty()){  //重號
				$double="重號樹在 (".$checkstemid[0]['qx']." ,".$checkstemid[0]['qy'].")(".$checkstemid[0]['sqx']." ,".$checkstemid[0]['sqy'].")";
				if ($entry == '3') {
				    $base=FsTreeBase::where('tag', 'like', $data[$i]['tag'])->first()->toArray();
				    $double="重號樹在 (".$base['qx']." ,".$base['qy'].")(".$base['sqx']." ,".$base['sqy'].")";
				}
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

			//2.2 tag的格式是否正確
			$tagab=str_split(trim($data[$i]['tag']));
			$qx1=str_pad($data[$i]['qx'],2,'0',STR_PAD_LEFT);  //在左側補0
			$qy1=str_pad($data[$i]['qy'],2,'0',STR_PAD_LEFT);
			if ($tagab[0]=='G'){
				if (count($tagab)!='7'){
					$datasavenote=$data[$i]['stemid'].' 牌號錯誤，請檢查。';
					$pass="0";break;
				} else if ($tagab[1].$tagab[2].$tagab[3].$tagab[4]!=$qx1.$qy1){
					$datasavenote=$data[$i]['stemid'].' 牌號錯誤，請檢查。';
					$pass="0";break;
				}
			} else {
				if (count($tagab)!='6'){
					$datasavenote=$data[$i]['stemid'].' 牌號錯誤，請檢查。';
					$pass="0";break;
				} else if ($tagab[0].$tagab[1]!=$qx1){
					$datasavenote=$data[$i]['stemid'].' 牌號錯誤，請檢查。';
					$pass="0";break;
				}
//順便檢查 3.3 dbh需>=1
				if ($data[$i]['dbh'] < 1){
					$datasavenote=$data[$i]['stemid'].' dbh/h高 需大於或等於 1。';
					$pass="0";break;
				}
			}

//dbh欄位不得為0
			if ($data[$i]['dbh']=='0'){
				$datasavenote=$data[$i]['stemid'].' dbh/h高 需大於或等於 1。';
				$pass="0";break;
			}

	// 8. 如果只有分支沒有主幹，不予新增
			if ($data[$i]['branch']!='0'){
				
				$stemid3=$table::where('tag', 'like', $data[$i]['tag'])->where('branch', 'like', '0')->get();

				if ($stemid3->isEmpty()){
					$datasavenote=$data[$i]['stemid'].' 此分支沒有主幹。';
					$pass="0";break;
				} else {
					$site1=$stemid3[0]['qx'].$stemid3[0]['qy'].$stemid3[0]['sqx'].$stemid3[0]['sqy'];
					$site2=$data[$i]['qx'].$data[$i]['qy'].$data[$i]['sqx'].$data[$i]['sqy'];
					if ($site1 != $site2){

							$datasavenote=$data[$i]['stemid'].' 分支與主幹需在同一小區。如為R，請將R分支之位置註記在note。';

							$pass="0";break;
					}
				}
			}
	

        //4. code CIPR
            //自動轉為大寫
            
            // 4.1  
            if ($data[$i]['code']!=''){
            	$codea=str_split($data[$i]['code']);
                // if (in_array("C",$codea)){
                //     $datasavenote=$data[$i]['stemid']." 新增樹不得有 C。";
                //     $pass='0';
                //     break;
                // }
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
        }


        $result=['pass'=>$pass, 'datasavenote'=>$datasavenote, 'data'=>$data2];

		return $result;

	}
}


