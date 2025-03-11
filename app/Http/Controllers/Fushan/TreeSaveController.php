<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Controller;

use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeCensus2;
use App\Models\FsTreeCensus1;
use App\Models\FsTreeCensus5;
use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;
use App\Models\FsTreeFixlog;
use App\Models\FsTreeComplete;

use App\Jobs\FsTreeDataCheck;
use App\Jobs\FsTreeRecruitCheck;

use App\Jobs\TreeAddButton;
use App\Jobs\TreeUpdateBase;
use App\Jobs\TreeUpdateCensusData;



//每木資料輸入後的所有儲存與刪除

class TreeSaveController extends Controller
{

    public function getTableInstance($entry) {
        if ($entry == '1') {
            return new FsTreeRecord1;
        } else {
            return new FsTreeRecord2;
        }
    }

//重新載入資料
//因有修改資料才需要重新載入資料，故表示輸入未完成，需將輸入完成註記刪除
//修改資料後需重新按輸入完成鈕

    public function getRedata($entry, $qx, $qy, $sqx, $sqy, $user){

        $table = $this->getTableInstance($entry);

        $redatas=$table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();

        $ob_redata = new TreeAddButton;
        $redata=$ob_redata->addbutton($redatas, $entry);

        $thisentry='entry'.$entry;
        $uplist[$thisentry]='0';
        $uplist[$thisentry.'Done']=$user;
        $uplist[$thisentry.'Done_at']=date("Y-m-d H:i:s");
        FsTreeComplete::where('qx', 'like', $qx)->where('qy', 'like', $qy)->update($uplist);
        $uplist2['compareDone']='';
        $uplist2['compareDone_at']='';

        FsTreeComplete::where('qx', 'like', $qx)->update($uplist2);

        return $redata; 

    }

//儲存輸入資料
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
            if ($data[$i]['date']==''){$data[$i]['date']='0000-00-00';}
            $data[$i]['code']=strtoupper($data[$i]['code']);  //轉為皆大寫

            $check = new FsTreeDataCheck;
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
                    $excludedKeys=['updated_id', 'updated_at', 'alternotetable'];
                    if (!in_array($key, $excludedKeys)){
                        if ($odata[0][$key] != $value){
                            if($value==Null){$value='';}
                            $uplist[$key]=$value;
                        }
                    }
                }
                if ($uplist!=[]){

                    $uplist['updated_id']=$user;

                    $table::where('stemid', 'like', $data[$i]['stemid'])->update($uplist);

                    $datasavenote='資料已儲存';
                }

            } else {
                $datasavenote=$datacheck['datasavenote'];
                break;
                

            }
        }//最外層

//         //重新載入資料

        $redata=$this->getRedata($entry, $data[0]['qx'], $data[0]['qy'], $data[0]['sqx'], $data[0]['sqy'], $user);

            return [
                'result' => 'ok',
                'data' => $redata,
                // 'codea' => $uplist,
                // 'test' => $arr3,
                'datasavenote' => $datasavenote

            ];
    }

//新增資料儲存
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
                $excludedKeys = ['code', 'tofix', 'note', 'confirm', 'alternote', 'status'];
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

            $check = new FsTreeRecruitCheck;
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
                $data[$i]['updated_id']=$user;
                $data[$i]['updated_at']=date("Y-m-d H:i:s");
                $data[$i]['confirm']='';
                // $data[$i]['tocheck']='';
                $data[$i]['alternote']='';
                $data[$i]['show']='1';
                if ($data[$i]['tofix']==Null){$data[$i]['tofix']='';}
                if ($data[$i]['note']==Null){$data[$i]['note']='';}
 //如果是漏資料 //復活
                if ($data[$i]['tofix']=='1'){
                    $data[$i]['status']='';
                    $odata=$table::where('stemid', 'like', $data[$i]['stemid'])->get();
                    $pass='1';
                    $temp=array_keys($splist, $data[$i]['csp']);
                    $data[$i]['spcode']=$temp[0];


                    foreach($data[$i] as $key => $value){
                        $excludedKeysall=['updated_id', 'updated_at', 'alternotetable'];
                        if (!in_array($key, $excludedKeysall)){
                            if ($odata[0][$key] != $value){
                                if($value==Null){$value='';}

                                $includeKeys = ['qx', 'qy', 'sqx', 'sqy', 'spcode', 'pom'];

                                if (in_array($key, $includeKeys)) {
                                    $uplistalter[$key] = $value;
                                    $recruitsavenote = $recruitsavenote."<br>".$data[$i]['tag'] .' 漏資料，但基本資料 '.$key.' 與原始資料不符。請確認編號、檢查舊資料，或洽管理員。';
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
                        $nonsavelist[$i]=$data[$i]; continue;
                    }

                    if ($uplist!=[]){

                        $uplist['updated_id']=$user;

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



            } else {  // $datacheck['pass']!=1
                $recruitsavenote=$recruitsavenote."<br>".$datacheck['datasavenote'];
                $nonsavelist[$i]=$data[$i];
                // break;

            }
        }//最外層


//         //重新載入資料
        if($sqx!=''){

            $redata=$this->getRedata($entry, $data[0]['qx'], $data[0]['qy'], $sqx, $sqy, $user);

        } else {
            $redata=[];
        }

        return [
            'result' => 'ok',
            'data' => $redata,
            'odata' => $data,
            'nonsavelist' => $nonsavelist,
            'recruitsavenote' => $recruitsavenote

        ];

    }

//刪除新增資料
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

            $redata=$this->getRedata($entry, $thisqx, $thisqy, $thissqx, $thissqy, $user);

            return [
                'result' => 'ok',
                // 'test'=> $test,
                'thispage' => $thispage,
                'recruit' => $redata,
                'datasavenote' => $datasavenote
            ];
    }
//儲存特殊修改
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

        $data2 = array_filter($data, function($value) {
            return $value !== null && $value !== '';
        });
        unset($data2['stemid']);

        if (!empty($data2)){

            $table = $this->getTableInstance($entry);

            $olddata=$table::where('stemid', 'like', $data['stemid'])->get()->toArray();
            //檢查alternote後是否重號

            if (isset($data2['tag'])){
                if (isset($data2['b'])){
                    $newstemid=$data2['tag'].".".$data2['b'];
                } else {
                    $newstemid=$data2['tag'].".".$olddata[0]['branch'];
                }
                
                $check=$table::where('stemid', 'like', $newstemid)->get()->toArray();
                if (count($check)>0){
                    $datasavenote=$olddata[0]['stemid'].'特殊修改後重號，故不儲存號碼部分。  ';
                    unset($data2['tag']);
                    if (isset($data2['b'])){unset($data2['b']);}
                    
                }
            }

            if (!empty($data2)){
            // 轉換為 JSON 字串
                $alterdata = json_encode($data2, JSON_UNESCAPED_UNICODE);

                if ($olddata[0]['alternote']!=$alterdata){
                    $uplist['alternote']=$alterdata;
                    $uplist['updated_id']=$user;
                    $table::where('stemid', 'like', $data['stemid'])->update($uplist);
                    $datasavenote=$datasavenote.'資料已儲存';
                }
            }
            
        }


    // 重新載入資料

        $site=$table::where('stemid', 'like', $data['stemid'])->get();

        $redata=$this->getRedata($entry, $site[0]['qx'], $site[0]['qy'], $site[0]['sqx'], $site[0]['sqy'], $user);


        return [
            'result' => 'ok',
            'datasavenote' => $datasavenote,
            'data' => $redata,
            'thispage' => $thispage
            // 'inlist'=>$sql
        ];        

    }

//刪除特殊修改

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

//輸入完成
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
                $uplist['updated_id']=$user;
                $table::where('stemid', 'like', $data[$i]['stemid'])->update($uplist);
            }
        }

        //是否有date=0000-00-00，show=1
        $data = $table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('date', 'like', '0000-00-00')->where('show', 'like', '1')->get();

        if (!$data->isEmpty()){
            $finishnote='有資料未輸入完成 [('.$data[0]['sqx'].', '.$data[0]['sqy'].') '.$data[0]['stemid'].']';
            $pass='0';
        }
        

                
        if ($pass=='1'){
            $data = $table::select('tag')->where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('show', 'like', '1')->groupBy('tag')->get()->toArray();

            // print_r($data);
            for($i=0;$i<count($data);$i++){
                $com1s=[];
                $com2s=[];
                // $com1s2=[];
                $com3s=[];
                $notes=[];


                $data1=$table::where('tag', 'like', $data[$i]['tag'])->where('show', 'like', '1')->orderBy('branch', 'asc')->get()->toArray();
                if (count($data1)>1){   //有分支
                    foreach($data1 as $data0){
                        $com1=$data0['qx'].$data0['qy'].$data0['sqx'].$data0['sqy'];
                        $codes[]=$data0['code'];
                        $com1s[]=$com1;
                        $com2s[]=$data0['status'];
                        $com3s[]=$data0['spcode'];
                        $notes[$data0['branch']]=$data0['note'];
                        
                    }

                    $com1s=array_values(array_unique($com1s));
                    $com2s=array_values(array_unique($com2s));
                    $com3s=array_values(array_unique($com3s));
                    if ($data1[0]['branch']!='0'){
                        $pass='0';
                        $finishnote=$data1[0]['tag'].' 缺少主幹資料，請新增主幹並勾選漏資料，主幹若死亡，status = -3。';
                        break;
                    } 
                    // else if ($data1[0]['branch']=='0' && $data1[0]['status']=='-3'){
                    //     $found = false;
                    //     foreach ($notes as $key => $note) {
                    //         // 檢查是否包含 'TAB=' 或 '牌留'
                    //         if (strpos($note, 'TAB=') !== false || strpos($note, '牌留') !== false) {
                    //             $found = true;

                    //             // 檢查 TAB= 後的數字是否與 key 相符
                    //             if (preg_match('/TAB=(\d+)/', $note, $matches)) {
                    //                 $tabNumber = $matches[1]; // 取得 TAB 後面的數字
                    //                 if ($tabNumber == $key) {

                    //                 } else {
                    //                     $pass='0';
                    //                     $finishnote=$data1[0]['tag']. "- {$key} 和 TAB= 的數字不相符\n";
                    //                     break 2;
                    //                 }
                    //             }
                    //             break; // 找到一個匹配就結束檢查
                    //         }
                    //     }

                    //     if (!$found) {
                    //         $pass='0';
                    //         $finishnote= $data1[0]['tag']."沒有找到包含 'TAB=' 或 '牌留' 的值\n";
                    //         break;
                    //     }

                    // }


                    if (count($com1s)>1){
                        if (!in_array("R", $codes)){
                            $pass='0';
                            $finishnote='[('.$com1s[0][2].', '.$com1s[0][3].') ('.$com1s[1][2].', '.$com1s[1][3].')'.$data1[0]['tag'].'] 同編號應有相同小區';
                            break;
                        } else {  //重新篩選非R的資料
                            $data2=$table::where('tag', 'like', $data[$i]['tag'])->where('show', 'like', '1')->where('code', 'not like', '%R%')->get()->toArray();
                            $com1s2=[];
                            if (count($data2)>1){
                                foreach($data2 as $data01){
                                    $com11=$data01['qx'].$data01['qy'].$data01['sqx'].$data01['sqy'];
                                    $com1s2[]=$com11;
                                }
                            }
                            //該號碼非R的其他植株需在同一小區
                            $com1s2=array_values(array_unique($com1s2));
                            if (count($com1s2)>1){
                                // print_r($com1s2); 
                                $pass='0';
                                $finishnote='[('.$com1s2[0][2].', '.$com1s2[0][3].') ('.$com1s2[1][2].', '.$com1s2[1][3].') '.$data2[0]['tag'].'] 同編號應有相同小區';
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
                    } else if (in_array('-3', $com2s)){
                        //不能全是-3
                        $pass='0';
                        $finishnote='[('.$data1[0]['sqx'].', '.$data1[0]['sqy'].') '.$data1[0]['tag'].'] 同編號的status不能全為-3';
                        break;
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
            $uplist[$thisentry.'Done']=$user;
            $uplist[$thisentry.'Done_at']=date("Y-m-d H:i:s");
            FsTreeComplete::where('qx', 'like', $qx)->where('qy', 'like', $qy)->update($uplist);
        }


        return [
            'result' => 'ok',
            // 'result2' => 'test11',
            'pass' => $pass,
            // 'data'=> $com1s2,

            'finishnote' => $finishnote
        ];

    }
//後端資料更正
    public function saveupdate (Request $request){

        $data_all = request()->all();
        $splist = $request->session()->get('splist');

        $user=$data_all['user'];
        $from=$data_all['from'];
        $base=$data_all['data1'][0];
        $plotType='fstree';

        $test='';
        $datasavenote='';

//base
        $ob_updateBase = new TreeUpdateBase;
        $result=$ob_updateBase->up($plotType, $data_all, $splist);
        $datasavenote=$result['datasavenote'];
        $thisstemid=$result['thisstemid'];
        $ostemid=$result['ostemid'];
        $newstemid=$result['newstemid'];
        $pass=$result['pass'];


        if ($pass=='1'){

            $stemidlist=[];
            
// //census
            for ($i = 1; $i <= 5; $i++) {
                $lastcensus=5;
                ${"census$i"} = $data_all['data2'][$i-1];
                if ($newstemid!=$ostemid){
                    ${"census$i"}['stemid']=$newstemid;
                    ${"census$i"}['tag']=$base['tag'];
                    ${"census$i"}['branch']=$base['branch'];
                }

                if ($i==1){
                    $census1['h']=$census1['h2'];
                }

                if ($i==3 || $i==4){
                    ${"census$i"}['tocheck']='';
                }
                
                // $table= new FsTreeCensus$i;
                switch ($i) {
                    case 1:$table= new FsTreeCensus1; break;
                    case 2:$table= new FsTreeCensus2; break;
                    case 3:$table= new FsTreeCensus3; break;
                    case 4:$table= new FsTreeCensus4; break;
                    case 5:$table= new FsTreeCensus5; break;
                }

                
                $temp= $table::where('stemid','like',$ostemid)->get()->toArray();
                if (count($temp)>0){
                    ${"ocensus$i"}=$temp[0];
                } else {
                    ${"ocensus$i"}=[];
                }

                $ob_updateData = new TreeUpdateCensusData;
                $result2=$ob_updateData->up($plotType, $i, $lastcensus, ${"census$i"}, ${"ocensus$i"}, $table, $data_all, $ostemid);
                if ($result2['datasavenote']!=''){
                    $datasavenote=$result2['datasavenote'];
                }
        

            }
        }

        


            return [
                'result' => 'ok',
                'thisstemid' => $thisstemid,
                'result2'=>$result,
                // 'codetemp' => $codetemp,
                'from'=> $from,
                // 'tag' => $census1['stemid'],
                'datasavenote' => $datasavenote

            ];
    }
 
//後端資料處理時刪除資料
//如果是分支，直接刪除
//如果是主幹，軟刪除
    public function deleteCensusData(Request $request){

        $data_all = request()->all();

        $user=$data_all['user'];
        $from=$data_all['from'];
        $stemid=$data_all['stemid'];


        $thisstemid='';
        $fixlog=[];
        $fixall=[];
        $datasavenote='';
        $uplist=[];

        //如果是分支，之前調查判斷錯誤，直接刪除資料
        //如果是主幹，物種鑑定錯誤，非需要做的種類，則全株包含分支皆軟刪除，保留以避免牌號誤用

        $stemidtemp=explode('.', $stemid);
        $tag=$stemidtemp[0];
        $b=$stemidtemp[1]; 

        if ($b!='0'){

            for ($j = 1; $j <= 5; $j++) {
                $temp=[];

                switch ($j) {
                    case '1':$table= new FsTreeCensus1; break;
                    case '2':$table= new FsTreeCensus2; break;
                    case '3':$table= new FsTreeCensus3; break;
                    case '4':$table= new FsTreeCensus4; break;
                    case '5':$table= new FsTreeCensus5; break;
                }
                
                $temp = $table::where('stemid', 'like', $stemid)->get()->toArray();

                if (count($temp)>0){

                    $table::where('stemid','like', $stemid)->delete();
                    $datasavenote='已刪除 '.$stemid.' 資料';
                    $fixlog['id']='0';
                    $fixlog['from']=$from;
                    $fixlog['type']='delete';
                    $fixlog['sheet']="census".$j;
                    $fixlog['qx']=substr($stemid, 0, 2);
                    $fixlog['stemid']=$stemid;
                    $fixlog['descript']='刪除此編號資料';
                    $fixlog['updated_id']=$user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                    FsTreeFixlog::insert($fixlog);
                    $thisstemid=$stemid;
                } 

            }
        } else {

            $uplist['deleted_at']=date("Y-m-d H:i:s");
            $uplist['updated_id']=$user;

            FsTreeBase::where('tag', 'like', $tag)->update($uplist);
                    $fixlog['id']='0';
                    $fixlog['from']=$from;
                    $fixlog['type']='delete';
                    $fixlog['sheet']='base';
                    $fixlog['qx']=substr($stemid, 0, 2);
                    $fixlog['stemid']=$tag;
                    $fixlog['descript']='軟刪除此編號base資料';
                    $fixlog['updated_id']=$user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                    FsTreeFixlog::insert($fixlog);
                    // $fixall[]=$fixlog;
            $baser=FsTreeBaseR::where('stemid', 'like', $stemid)->get()->toArray();
            if (count($baser)>0){
                FsTreeBaseR::where('stemid', 'like', $stemid)->update($uplist);
                    $fixlog['sheet']='base_r';
                    FsTreeFixlog::insert($fixlog);
            }

            
                    

            for ($j = 1; $j <= 5; $j++) {
                $temp=[];
                $fixlog=[];
                switch ($j) {
                    case '1':$table= new FsTreeCensus1; break;
                    case '2':$table= new FsTreeCensus2; break;
                    case '3':$table= new FsTreeCensus3; break;
                    case '4':$table= new FsTreeCensus4; break;
                    case '5':$table= new FsTreeCensus5; break;
                }
                
                $temp = $table::where('tag', 'like', $tag)->get()->toArray();

                if (count($temp)>0){

                    $table::where('tag', 'like', $tag)->update($uplist);
                    $datasavenote='已刪除 '.$tag.' 所有資料';
                    $fixlog['id']='0';
                    $fixlog['from']=$from;
                    $fixlog['type']='delete';
                    $fixlog['sheet']="census".$j;
                    $fixlog['qx']=substr($stemid, 0, 2);
                    $fixlog['stemid']=$tag;
                    $fixlog['descript']='軟刪除此編號所有植株資料';
                    $fixlog['updated_id']=$user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                    // $fixall[]=$fixlog;
                    FsTreeFixlog::insert($fixlog);
                } 

            }

        }

       


            return [
                'result' => 'ok',
                'thisstemid' => $thisstemid,
                'from'=>$from,
                // 'uplist' => $base_uplist,
                // 'tag' => $census1['stemid'],
                'datasavenote' => $datasavenote

            ];


    }
//後端處理時新增資料
//可能是在調查完後進樣區查資料時發現的漏新增樹
//直接新增在census5大表
    
    public function addData(Request $request){
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
            $inlist=[];
            $inlist2=[];


        for($i=0; $i<count($data);$i++){
            $pass='1';
            $fixlog1=[];
            $fixlog2=[];


            if (is_null($data[$i]['date']) || $data[$i]['date']==''){
                $q=$q+1;
                $nonsavelist[$i]=$data[$i];
                continue;

            }

            foreach ($data[$i] as $key => $value){
                $excludedKeys = ['code', 'tofix', 'note', 'confirm', 'alternote', 'status'];
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

            $check = new FsTreeRecruitCheck;
            $datacheck=$check->check($data[$i], $entry);

            if ($datacheck['pass']==1){
            $inlist=[];
            $inlist2=[];

            // 6.1 樹蕨之dbh&h高
                if ($data[$i]['stemid'][0]=='G'){
                    $data[$i]['h2']=$data[$i]['dbh'];
                    $data[$i]['dbh']='0';
                    $data[$i]['h1']=$data[$i]['pom'];

                } else {
                    $data[$i]['h2']='0';
                    $data[$i]['h1']='0';
                }
                $data[$i]['updated_id']=$user;
                $data[$i]['updated_at']=date("Y-m-d H:i:s");

                if ($data[$i]['note']==Null){$data[$i]['note']='';}

//如果是新增樹
// 8.1 新增樹種需要spcode
                    $temp=array_keys($splist, $data[$i]['csp']);
                    $data[$i]['spcode']=$temp[0];
                    $data[$i]['status']='-9';
                    // $data2=$data[$i];
                //新增
//獲得census5 key

                    $census5key=FsTreeCensus5::first()->toArray();
                    foreach ($census5key as $key5 =>$value5){
                        if (isset($data[$i][$key5])){

                            $inlist[$key5]=$data[$i][$key5];
                        } else {
                            $inlist[$key5]='';
                        }
                    }

                    $inlist['alternote']="{\"other\":\"由後端新增資料\"}";

                    FsTreeCensus5::insert($inlist);
//主幹才需新增base資料
                    if($data[$i]['branch']=='0'){
						
                            switch ($data[$i]['sqx']) {
                                case '1':
                                case '2':
                                    $data[$i]['subqx'] = '1';
                                    break;
                                case '3':
                                case '4':
                                    $data[$i]['subqx'] = '2';
                                    break;
                                default:
                                    $data[$i]['subqx']; // 保留原始值
                                    break;
                            }

                            switch ($data[$i]['sqy']) {
                                case '1':
                                case '2':
                                    $data[$i]['subqy'] = '1';
                                    break;
                                case '3':
                                case '4':
                                    $data[$i]['subqy'] = '2';
                                    break;
                                default:
                                    $data[$i]['subqy']; // 保留原始值
                                    break;
                            }						
                        $basekey=FsTreeBase::first()->toArray();
                        foreach ($basekey as $keybase =>$valuebase){
                            if (isset($data[$i][$keybase])){
                                $inlist2[$keybase]=$data[$i][$keybase];
                            } else {
                                $inlist2[$keybase]='0';
                            }
                        }
                        $inlist2['deleted_at']='';
                        FsTreeBase::insert($inlist2);
                    }

                    $nonsavelist[$i]['qx']='';
                    $nonsavelist[$i]['qy']='';
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
//fixlog
                    
                    $fixlog1['id']='0';
                    $fixlog1['from']='addData';
                    $fixlog1['type']='insert';
                    $fixlog1['sheet']="census5";
                    $fixlog1['qx']=$data[$i]['qx'];
                    $fixlog1['stemid']=$data[$i]['stemid'];
                    $fixlog1['descript']=json_encode($inlist, JSON_UNESCAPED_UNICODE);
                    $fixlog1['updated_id']=$user;
                    $fixlog1['updated_at']=date("Y-m-d H:i:s");
                    FsTreeFixlog::insert($fixlog1);


                    if ($inlist2!=[]){
                        $fixlog2['id']='0';
                        $fixlog2['from']='addData';
                        $fixlog2['type']='insert';
                        $fixlog2['sheet']="base";
                        $fixlog2['qx']=$data[$i]['qx'];
                        $fixlog2['stemid']=$data[$i]['stemid'];
                        $fixlog2['descript']=json_encode($inlist2, JSON_UNESCAPED_UNICODE);
                        $fixlog2['updated_id']=$user;
                        $fixlog2['updated_at']=date("Y-m-d H:i:s");
                        FsTreeFixlog::insert($fixlog2);
                    }

                    $recruitsavenote.="<br>第".($i+1).'筆資料 ('.$data[$i]['stemid'].') 已儲存 ';

            } else {  // $datacheck['pass']!=1
                $recruitsavenote=$recruitsavenote."<br>".$datacheck['datasavenote'];
                $nonsavelist[$i]=$data[$i];
                // break;

            }
        }//最外層

          

        return [
            'result' => 'ok',

            'odata' => $data,
            'nonsavelist' => $nonsavelist,
            // 'q'=>$q,
            // 'uplistalter' => $uplistalter,
            'pass' => $inlist2,
            // 'entry' => $entry,
            // 'test' => $arr3,
            'recruitsavenote' => $recruitsavenote

        ];

    }

}

