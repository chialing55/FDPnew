<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeEntrycom;

use App\Jobs\fsTreeDataCheck;
use App\Jobs\fsTreeRecruitCheck;
use App\Jobs\fsTreeAddButton;
use fsTreeAlternote;


class fsTreeSaveController extends Controller
{

    public function getTableInstance($entry) {
        if ($entry == '1') {
            return new FsTreeRecord1;
        } else {
            return new FsTreeRecord2;
        }
    }


    public function getRedata($entry, $qx, $qy, $sqx, $sqy, $user){

        $table = $this->getTableInstance($entry);

        $redatas=$table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();

        $ob_redata = new fsTreeAddButton;
        $redata=$ob_redata->addbutton($redatas, $entry);

        $thisentry='entry'.$entry;
        $uplist[$thisentry]='0';
        $uplist['update_id'.$entry]=$user;
        $uplist['update_date'.$entry]=date("Y-m-d H:i:s");
        FsTreeEntrycom::where('qx', 'like', $qx)->where('qy', 'like', $qy)->update($uplist);

        return $redata;

    }


    public function savedata (Request $request){

        $data_all = request()->all();
        $splist = $request->session()->get('splist');
        $data=$data_all['data'];
        $entry=$data_all['entry'];
        $user=$data_all['user'];
        $test='';

        $datasavenote='';
        
        $table = $this->getTableInstance($entry);

        for($i=0; $i<count($data);$i++){
            $data[$i]['stemid']=$data[$i]['tag'].".".$data[$i]['branch'];
            $uplist=[];
            // $datacheck=['pass'=>'1', 'datasavenote'=>''];

            $check = new fsTreeDataCheck;
            $datacheck=$check->check($data[$i]);

            if ($datacheck['pass']==1){

                $odata=$table::where('stemid', 'like', $data[$i]['stemid'])->get()->toArray();

            // 6.1 樹蕨之dbh&h高
                if ($data[$i]['stemid'][0]=='G'){
                    $data[$i]['h2']=$data[$i]['dbh'];
                    $data[$i]['dbh']='0';
                    // $data[$i]['h1']=$data[$i]['pom'];
                }
            // 8.1 新增樹種更改中文名時，需要spcode
                if ($data[$i]['status']=='-9'){
                    if ($data[$i]['csp']!=$odata[0]['csp']){
                        $temp=array_keys($splist,$data[$i]['csp']);
                        $data[$i]['spcode']=$temp[0];
                    }
                }

            //更新
                foreach($data[$i] as $key => $value){
                    $excludedKeys=['update_id', 'updated_at', 'alternotetable'];
                    if (!in_array($key, $excludedKeys)){
                        if ($odata[0][$key] != $value){
                            if($value==Null){$value='';}
                            $uplist[$key]=$value;
                        }
                    }
                }
                if ($uplist!=[]){

                    $uplist['update_id']=$user;

                    $table::where('stemid', 'like', $data[$i]['stemid'])->update($uplist);

                    $datasavenote='資料已儲存';
                }

            } else {
                $datasavenote=$datacheck['datasavenote'];
                break;
                

            }
        }//最外層

//         //重新載入資料
        // $redatas=$table::where('qx', 'like', $data[0]['qx'])->where('qy', 'like', $data[0]['qy'])->where('sqx', 'like', $data[0]['sqx'])->where('sqy', 'like', $data[0]['sqy'])->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();


        // $ob_redata = new fsTreeAddButton;
        // $redata=$ob_redata->addbutton($redatas, $entry);

        $redata=$this->getRedata($entry, $data[0]['qx'], $data[0]['qy'], $data[0]['sqx'], $data[0]['sqy'], $user);

            return [
                'result' => 'ok',
                'data' => $redata,
                // 'codea' => $uplist,
                // 'test' => $arr3,
                'datasavenote' => $datasavenote

            ];
    }


    public function saverecruit(Request $request){
        $splist = $request->session()->get('splist');
        $data_all = request()->all();

        $data=$data_all['data'];
        $entry=$data_all['entry'];
        $user=$data_all['user'];
        $recruitsavenote='';
        $datacheck='';
        $uplistalter='';
        $q=0;
        $nonsavelist=[];
        $sqx='';
        $sqy='';

        $table = $this->getTableInstance($entry);

        for($i=0; $i<count($data);$i++){
            $pass='1';
            $inlist=[];
            $uplistalter=[];
            $uplist=[];

            if (is_null($data[$i]['date']) || $data[$i]['date']==''){
                $q=$q+1;
                $nonsavelist[$i]=$data[$i];
                continue;

            }

            foreach ($data[$i] as $key => $value){
                $excludedKeys = ['code', 'tofix', 'note'];
                if (!in_array($key, $excludedKeys) && is_null($value)) {
                    $pass = '0';
                    $recruitsavenote = $recruitsavenote."<br> 第".($i+1)."筆 ".$data[$i]['tag'] ." ". $key.'資料不全，不予處理。';
                    break;  //離開檢查
                } 
            }
            
            if ($pass=='0') {$nonsavelist[$i]=$data[$i]; continue;} //跳過這筆資料
            else {
                    $sqx=$data[$i]['sqx'];
                    $sqy=$data[$i]['sqy'];}   

            $data[$i]['tag']=strtoupper($data[$i]['tag']);
            $data[$i]['code']=strtoupper($data[$i]['code']);
            $data[$i]['stemid']=$data[$i]['tag'].".".$data[$i]['branch'];
            
            
            $datacheck=['pass'=>'1', 'datasavenote'=>''];

            $check = new fsTreeRecruitCheck;
            $datacheck=$check->check($data[$i], $entry);

            if ($datacheck['pass']==1){


            // 6.1 樹蕨之dbh&h高
                if ($data[$i]['stemid'][0]=='G'){
                    $data[$i]['h2']=$data[$i]['dbh'];
                    $data[$i]['dbh']='0';
                    $data[$i]['h1']=$data[$i]['pom'];

                } else {
                    $data[$i]['h2']='0';
                    $data[$i]['h1']='0';
                }
                $data[$i]['update_id']=$user;
                $data[$i]['updated_at']=date("Y-m-d H:i:s");
                $data[$i]['confirm']='';
                // $data[$i]['tocheck']='';
                $data[$i]['alternote']='';
                $data[$i]['show']='1';
                if ($data[$i]['tofix']==Null){$data[$i]['tofix']='';}
                if ($data[$i]['note']==Null){$data[$i]['note']='';}
 //如果是漏資料 
                if ($data[$i]['tofix']=='1'){
                    $data[$i]['status']='';
                    $odata=$table::where('stemid', 'like', $data[$i]['stemid'])->get();
                    $pass='1';
                    $temp=array_keys($splist, $data[$i]['csp']);
                    $data[$i]['spcode']=$temp[0];

                    foreach($data[$i] as $key => $value){
                        $excludedKeysall=['update_id', 'updated_at', 'alternotetable'];
                        if (!in_array($key, $excludedKeysall)){
                            if ($odata[0][$key] != $value){
                                if($value==Null){$value='';}
                                

                                $includeKeys = ['qx', 'qy', 'sqx', 'sqy', 'spcode', 'pom', 'h1'];

                                if (in_array($key, $includeKeys)) {
                                    $uplistalter[$key] = $value;
                                    $recruitsavenote = $recruitsavenote."<br>".$data[$i]['tag'] . ' 漏資料，但基本資料與原始資料不符。請確認編號、檢查舊資料，或洽管理員。';
                                    $pass = '0';
                                } else {
                                    $uplist[$key] = $value;
                                }
                            }
                        }
                    }

                    // $pass='0';
                    // $recruitsavenote='測試中';

                    if ($pass=='0'){
                        $nonsavelist[$i]=$data[$i]; break;
                    }

                    if ($uplist!=[]){

                        $uplist['update_id']=$user;

                        $table::where('stemid', 'like', $data[$i]['stemid'])->update($uplist);

                    }

                } else {
//如果是新增樹
// 8.1 新增樹種需要spcode
                    $temp=array_keys($splist, $data[$i]['csp']);
                    $data[$i]['spcode']=$temp[0];
                    $data[$i]['status']='-9';
                    // $data2=$data[$i];
                //新增
                    foreach($data[$i] as $key => $value){
                        $inlist[$key]=$value;
                    }
                    $inlist2=$inlist;
                    $table::insert($inlist);
                }
                $recruitsavenote=$recruitsavenote."<br>第".($i+1).'筆資料已儲存';
                    $nonsavelist[$i]['qx']=$data[$i]['qx'];
                    $nonsavelist[$i]['qy']=$data[$i]['qy'];
                    $nonsavelist[$i]['branch']='0';
                    $nonsavelist[$i]['pom']='1.3';
                    $nonsavelist[$i]['date']='';
                    $nonsavelist[$i]['code']='';
                    $nonsavelist[$i]['sqx']='';
                    $nonsavelist[$i]['sqy']='';
                    $nonsavelist[$i]['tag']='';
                    $nonsavelist[$i]['csp']='';
                    $nonsavelist[$i]['dbh']='';
                    $nonsavelist[$i]['note']='';
                    $nonsavelist[$i]['tofix']='';



            } else {  //if ($datacheck['pass']==1)
                $recruitsavenote=$recruitsavenote."<br>".$datacheck['datasavenote'];
                $nonsavelist[$i]=$data[$i]; continue;
                // break;
                

            }
        }//最外層


//         //重新載入資料
        if($sqx!=''){
            // $redatas=$table::where('qx', 'like', $data[0]['qx'])->where('qy', 'like', $data[0]['qy'])->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();


            // $ob_redata = new fsTreeAddButton;
            // $redata=$ob_redata->addbutton($redatas, $entry);

            $redata=$this->getRedata($entry, $data[0]['qx'], $data[0]['qy'], $sqx, $sqy, $user);

        } else {
            $redata=[];
        }

        return [
            'result' => 'ok',
            'data' => $redata,
            'odata' => $data,
            'nonsavelist' => $nonsavelist,
            // 'q'=>$q,
            // 'uplistalter' => $uplistalter,
            // 'pass' => $inlist2,
            // 'entry' => $entry,
            // 'test' => $arr3,
            'recruitsavenote' => $recruitsavenote

        ];

    }


    public function deletedata(Request $request, $stemid, $entry, $thispage){
        $test='';
            $user = $request->session()->get('user', function () {
                return view('login1', [
                'check' => 'no'
                ]);
            });
        // $user='chialing';
        $datasavenote='';

        $table = $this->getTableInstance($entry);

          
            $thisdata=$table::where('stemid','like', $stemid)->get();
            $thisqx=$thisdata[0]['qx'];
            $thisqy=$thisdata[0]['qy'];
            $thissqx=$thisdata[0]['sqx'];
            $thissqy=$thisdata[0]['sqy'];
            $total=$table::where('qx', 'like', $thisqx)->where('qy', 'like', $thisqy)->where('sqx', 'like', $thissqx)->where('sqy', 'like', $thissqy)->orderBy('stemid', 'asc')->get();

            $d_record = $table::where('stemid', $stemid)->delete();

                // $test='y';

            $datasavenote='已刪除 '.$stemid.' 新增樹資料';


            // 重新載入資料
            // $redatas=$table::where('qx', 'like', $thisqx)->where('qy', 'like', $thisqy)->where('sqx', 'like', $thissqx)->where('sqy', 'like', $thissqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();

            // $ob_redata = new fsTreeAddButton;
            // $redata=$ob_redata->addbutton($redatas, $entry);
            $redata=$this->getRedata($entry, $thisqx, $thisqy, $thissqx, $thissqy, $user);

            return [
                'result' => 'ok',
                // 'test'=> $test,
                'thispage' => $thispage,
                'recruit' => $redata,

                'datasavenote' => $datasavenote
            ];
    }

    public function savealternote(Request $request){

        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });

        $data_all = request()->all();
        $data=$data_all['data'][0];
        $entry=$data_all['entry'];
        $thispage=$data_all['thispage'];
        $inlist=[];
        $datasavenote='';

        $data2 = array_filter($data);
        unset($data2['stemid']);

        if (!empty($data2)){

            // 轉換為 JSON 字串
            $alterdata = json_encode($data2, JSON_UNESCAPED_UNICODE);

            $table = $this->getTableInstance($entry);
          
            $olddata=$table::where('stemid', 'like', $data['stemid'])->get()->toArray();

            if ($olddata[0]['alternote']!=$alterdata){
                $uplist['alternote']=$alterdata;
                $uplist['update_id']=$user;
                $table::where('stemid', 'like', $data['stemid'])->update($uplist);
            }
            $datasavenote='資料已儲存';
        }


    // 重新載入資料

        $site=$table::where('stemid', 'like', $data['stemid'])->get();
        // $redatas=$table::where('qx', 'like', $site[0]['qx'])->where('qy', 'like', $site[0]['qy'])->where('sqx', 'like', $site[0]['sqx'])->where('sqy', 'like', $site[0]['sqy'])->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();
        // // $redata='1';


        // $ob_redata = new fsTreeAddButton;
        // $redata=$ob_redata->addbutton($redatas, $entry);

        $redata=$this->getRedata($entry, $site[0]['qx'], $site[0]['qy'], $site[0]['sqx'], $site[0]['sqy'], $user);


        return [
            'result' => 'ok',
            'datasavenote' => '資料已儲存',
            'data' => $redata,
            'thispage' => $thispage
            // 'inlist'=>$sql
        ];        

    }

    public function deletealter(Request $request, $stemid, $entry, $thispage){

        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        $table = $this->getTableInstance($entry);
        $datasavenote='';
       

            
        $table::where('stemid', 'like', $stemid)->update(['alternote'=>'']);
                // $test='y';

        $datasavenote='已刪除 '.$stemid.' 特殊修改資料';


        // 重新載入資料

        $site=$table::where('stemid', 'like', $stemid)->get();
        // $redatas=$table::where('qx', 'like', $site[0]['qx'])->where('qy', 'like', $site[0]['qy'])->where('sqx', 'like', $site[0]['sqx'])->where('sqy', 'like', $site[0]['sqy'])->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();

        // $ob_redata = new fsTreeAddButton;
        // $redata=$ob_redata->addbutton($redatas, $entry);

        $redata=$this->getRedata($entry, $site[0]['qx'], $site[0]['qy'], $site[0]['sqx'], $site[0]['sqy'], $user);

        $realterdata=['stemid'=>$stemid, 'qx'=>'', 'qy' => '', 'sqx'=>'', 'sqy' => '', 'tag'=>'', 'b'=>'', 'csp'=>'', 'pom'=>''];
        $havedata='no';

        return [
            'result' => 'ok',
            // 'test'=> $test,
            'thispage' => $thispage,
            'data' => $redata,
            'realterdata' => $realterdata,
            'havedata' => $havedata,

            'datasavenote' => $datasavenote
        ];


    }


    public function finishnote(Request $request, $qx, $qy, $entry){

        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });

        $splist = $request->session()->get('splist');

        $table = $this->getTableInstance($entry);
        $pass='1';
        $finishnote='';
        $data = $table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->get()->toArray();

        //如果沒有csp，先補上
        for($i=0;$i<count($data);$i++){
            if ($data[$i]['csp']==''){
                $uplist['csp']=$splist[$data[$i]['spcode']];
                $uplist['update_id']=$user;
                $table::where('stemid', 'like', $data[$i]['stemid'])->update($uplist);
            }
        }

        //是否有date=0000-00-00，show=1
        $data = $table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->get();

        if (!$data->isEmpty()){
            $finishnote='有資料未輸入完成 [('.$data[0]['sqx'].', '.$data[0]['sqy'].') '.$data[0]['stemid'].']';
            $pass='0';
        }

        //同tag是否status相同，csp相同，小區相同, show=1

        if ($pass=='1'){
            $data = $table::select('tag')->where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('show', 'like', '1')->groupBy('tag')->get()->toArray();

            // print_r($data);
            for($i=0;$i<count($data);$i++){
                $com1s=[];
                $com2s=[];
                $com1s2=[];
                $com3s=[];
                $data1=$table::where('tag', 'like', $data[$i]['tag'])->where('show', 'like', '1')->get()->toArray();
                if (count($data1)>1){
                    foreach($data1 as $data0){
                        $com1=$data0['qx'].$data0['qy'].$data0['sqx'].$data0['sqy'];
                        $codes[]=$data0['code'];
                        $com1s[]=$com1;
                        $com2s[]=$data0['status'];
                        $com3s[]=$data0['spcode'];
                        
                    }

                    $com1s=array_unique($com1s);
                    $com2s=array_unique($com2s);
                    $com3s=array_unique($com3s);
                    if (count($com1s)>1){
                        if (!in_array("R", $codes)){
                            $pass='0';
                            $finishnote='[('.$com1s[0][3].', '.$com1s[0][4].') ('.$com1s[1][3].', '.$com1s[1][4].')'.$data1[0]['tag'].'] 同編號應有相同小區';
                            break;
                        } else {  //重新篩選非R的資料
                            $data2=$table::where('tag', 'like', $data[$i]['tag'])->where('show', 'like', '1')->where('code', 'not like', '%R%')->get()->toArray();
                            if (count($data2)>1){
                                foreach($data1 as $data0){
                                    $com1=$data0['qx'].$data0['qy'].$data0['sqx'].$data0['sqy'];
                                    $com1s2[]=$com1;
                                }
                            }
                            $com1s2=array_unique($com1s2);
                            if (count($com1s2)>1){
                                $finishnote='[('.$com1s2[0][3].', '.$com1s2[0][4].') ('.$com1s2[1][3].', '.$com1s2[1][4].') '.$data2[0]['tag'].'] 同編號應有相同小區';
                                break;
                            }


                        }
                    }

                    if (count($com2s)>1){
                        //-9
                        if (in_array('0', $com2s) || in_array('-1', $com2s) || in_array('-2', $com2s)){
                            // print_r($com2s);
                            $pass='0';
                            $finishnote='[('.$data1[0]['sqx'].', '.$data1[0]['sqy'].') '.$data1[0]['tag'].'] 同編號應有相同status';
                            break;
                        }

                    }

                    if (count($com3s)>1){
                        $pass='0';
                        
                        $finishnote='[('.$data1[0]['sqx'].', '.$data1[0]['sqy'].') '.$data1[0]['tag'].'] 同編號應有相同csp';
                        break;
                        
                    }
                }

            }


        }

        if ($pass==1){
            $finishnote="輸入完成";
            $thisentry='entry'.$entry;
            $uplist[$thisentry]='1';
            $uplist['update_id'.$entry]=$user;
            $uplist['update_date'.$entry]=date("Y-m-d H:i:s");
            FsTreeEntrycom::where('qx', 'like', $qx)->where('qy', 'like', $qy)->update($uplist);            
        }


        return [
            'result' => 'ok',
            'pass' => $pass,
            // 'test'=> $splist,

            'finishnote' => $finishnote
        ];

    }

}

