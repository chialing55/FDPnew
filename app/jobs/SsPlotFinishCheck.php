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

class SsPlotFinishCheck
{
	public function check(Request $request, $tabEnvi, $table, $col, $plotType){

        $finishnote='';

        $envi=$tabEnvi::query()->get()->toArray();

        for($i=0;$i<count($envi);$i++){
            foreach ($envi[$i] as $key=>$value){
                if ($value ==''){
                    if ($plotType=='ss1ha'){
                        $envi[$i]['plot']="(".$envi[$i]['qx'].", ".$envi[$i]['qy'].")";
                    }

                    $finishnote.=$envi[$i]['plot'].' 環境資料有空值。<br>';
                    break;
                }
            }
        }



//檢查每木資料

        $data=$table::query()->where('show', 'like', '1')->get()->toArray();

        for($i=0;$i<count($data);$i++){
//確認是否有ill
            if ($plotType=='ss1ha'){
                $data[$i]['plot']=$data[$i]['qx'].$data[$i]['qy'];
                $data[$i]['plot2']="(".$data[$i]['qx'].", ".$data[$i]['qy'].")(".$data[$i]['sqx'].", ".$data[$i]['sqy'].")";
            } else {
                $data[$i]['plot2']="(".$data[$i]['plot'].")(".$data[$i]['sqx'].", ".$data[$i]['sqy'].")";
            }


            if ($data[$i]['code']=='-9' || $data[$i]['code']==''){
                if ($data[$i]['ill']=='0'){
                $finishnote.=$data[$i]['plot2']." ".$data[$i]['tag']."-".$data[$i]['branch'].' ill 不得為0。<br>';
                }
            }
//同tag是否status相同，csp相同，小區相同
            if ($data[$i]['branch']!='0'){

                if ($plotType=='ss1ha'){
                    $dataMain=$table::where('tag', 'like', $data[$i]['tag'])->where('branch', 'like', '0')->where('show', 'like', '1')->first()->toArray();
                    $dataMain['plot']=$dataMain['qx'].$dataMain['qy'];

                } else {
                    $dataMain=$table::where('plot', 'like', $data[$i]['plot'])->where('tag', 'like', $data[$i]['tag'])->where('branch', 'like', '0')->where('show', 'like', '1')->first()->toArray();

                }


                if ($dataMain==[]){
                    $finishnote.=$data[$i]['stemid'].' 缺少主幹。<br>';
                } else {
                    if ($data[$i]['status']=='-3' || $data[$i]['status']=='-9' || $dataMain['status']=='-4' || $dataMain['status']=='-3'){
                        $temp=$dataMain['plot'].$dataMain['sqx'].$dataMain['sqy'].$dataMain['csp'];
                        $temp2=$data[$i]['plot'].$data[$i]['sqx'].$data[$i]['sqy'].$data[$i]['csp'];
                        if ($data[$i]['code']=='F'){
                            $temp=$dataMain['csp'];
                            $temp2=$data[$i]['csp'];
                        }
                    } else {
                        $temp=$dataMain['sqx'].$dataMain['sqy'].$dataMain['csp'].$dataMain['status'];
                        $temp2=$data[$i]['sqx'].$data[$i]['sqy'].$data[$i]['csp'].$data[$i]['status'];
                        if ($data[$i]['code']=='F'){
                            $temp=$dataMain['csp'].$dataMain['status'];
                            $temp2=$data[$i]['csp'].$data[$i]['status'];
                        }                        
                    }


                    if($temp!=$temp2){
                        $finishnote.=$data[$i]['plot2']." ".$data[$i]['tag']."-".$data[$i]['branch'].' 與主幹 5x/5y/csp/status 資料不符。<br>';
                    }
                }
            }
        }

		return $finishnote;

	}
}


