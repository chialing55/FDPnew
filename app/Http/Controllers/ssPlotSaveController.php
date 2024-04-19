<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\Ss10mQuad2014;
use App\Models\Ss10mTree2014;
use App\Models\Ss10mTree2015;
use App\Models\Ss10mTree2024;
use App\Models\Ss10mTreeEnviR1;
use App\Models\Ss10mTreeEnviR2;
use App\Models\Ss10mTreeCovR1;
use App\Models\Ss10mTreeCovR2;
use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;
use App\Models\Ss1haData2015;
use App\Models\Ss1haData2024;
use App\Models\Ss1haBase2024;
use App\Models\Ss1haBaseR2024;
use App\Models\Ss1haRecord1;
use App\Models\Ss1haRecord2;
use App\Models\Ss1haEnviR1;
use App\Models\Ss1haEnviR2;
use App\Models\SsEntrycom;
use App\Models\SsFixlog;
use App\Models\SsSplist;

use App\Jobs\SsPlotDataCheck;
use App\Jobs\SsPlotRecruitCheck;
use App\Jobs\FsTreeAddButton;
use App\Jobs\TreeUpdateBase;
use App\Jobs\TreeUpdateCensusData;


class SsPlotSaveController extends Controller
{

    public function get10mEnviTableInstance($entry) {
        if ($entry == '1') {
            return new Ss10mTreeEnviR1;
        } else {
            return new Ss10mTreeEnviR2;
        }
    }


    public function get10mCovTableInstance($entry) {
        if ($entry == '1') {
            return new Ss10mTreeCovR1;
        } else {
            return new Ss10mTreeCovR2;
        }
    }


    public function get10mDataTableInstance($entry) {
        if ($entry == '1') {
            return new Ss10mTreeRecord1;
        } else {
            return new Ss10mTreeRecord2;
        }
    }

    public function get1haEnviTableInstance($entry) {
        if ($entry == '1') {
            return new Ss1haEnviR1;
        } else {
            return new Ss1haEnviR2;
        }
    }


    public function get1haDataTableInstance($entry) {
        if ($entry == '1') {
            return new Ss1haRecord1;
        } else {
            return new Ss1haRecord2;
        }
    }

    public function get10mRedata($entry, $plot, $sqx, $sqy, $user){

        $table = $this->get10mDataTableInstance($entry);

        $redatas=$table::where('plot', 'like', $plot)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();

        $ob_redata = new FsTreeAddButton;
        $redata=$ob_redata->addbutton($redatas, $entry);

        $col='entry'.$entry.'com';

        $entrycomUpdate=SsEntrycom::query()->where('plot', 'like', '10m')->update([$col => '', 'update_id'=>$user]);

        return $redata;

    }

    public function get1haRedata($entry, $qx, $qy, $sqx, $sqy, $user){

        $table = $this->get1haDataTableInstance($entry);

        $redatas=$table::where('qx', 'like', $qx)->where('qy', 'like', $qy)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->where('show', 'like', '1')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get();

        $ob_redata = new FsTreeAddButton;
        $redata=$ob_redata->addbutton($redatas, $entry);

        $col='entry'.$entry.'com';

        $entrycomUpdate=SsEntrycom::query()->where('plot', 'like', '1ha')->update([$col => '', 'update_id'=>$user]);

        return $redata;

    }

    public function get10mRecovdata($entry, $plot, $sqx, $sqy, $user){

        $table = $this->get10mCovTableInstance($entry);
        $redata=[];

        $redata=$table::where('plot', 'like', $plot)->where('sqx', 'like', $sqx)->where('sqy', 'like', $sqy)->orderBy('sqx', 'asc')->orderBy('sqy', 'asc')->orderBy('layer', 'desc')->orderBy('id', 'asc')->get();


            if (!$redata->isEmpty()){
                $redata=$redata->toArray();
                for($m=0;$m<count($redata);$m++){
                    $deleteid = htmlspecialchars($redata[$m]['id']);
                    $escapedEntry = htmlspecialchars($entry);


                    $redata[$m]['delete']="<button class='deletecov' onclick='deletecov(\"$deleteid\", \"$escapedEntry\")' deleteid='".$redata[$m]['id']."' entry='".$entry."' plot='".$plot."'>X</button>";
                }
            } else {
                $redata=[];
            }

        $col='entry'.$entry.'com';

        $entrycomUpdate=SsEntrycom::query()->where('plot', 'like', '10m')->update([$col => '', 'update_id'=>$user]);

        return $redata;
    }

    public function saveenvi (Request $request){
        $data_all = request()->all();
        // $splist = $request->session()->get('splist');
        $envi=$data_all['data'][0];
        $entry=$data_all['entry'];
        $user=$data_all['user'];
        $plotType=$data_all['plotType'];

        $envisavenote='';

        foreach ($envi as $key => $value){
            if ($value==null){$value='';}
            $uplist[$key]=$value;
        }
        $uplist['update_id']=$user;

        if ($plotType=='ss10m'){
            $table = $this->get10mEnviTableInstance($entry);
            $table::where('plot', $envi['plot'])->update($uplist);
            $plotvalue='10m';
        } else {
            $table = $this->get1haEnviTableInstance($entry);
            $table::where('qx', $envi['qx'])->where('qy', $envi['qy'])->update($uplist);
            $plotvalue='1ha';
        }

        $col='entry'.$entry.'com';

        $entrycomUpdate=SsEntrycom::query()->where('plot', 'like', $plotvalue)->update([$col => '', 'update_id'=>$user]);


        $envisavenote='已儲存環境資料';

// echo 'hi';
            return [
                'result' => 'ok',
                'envi' => $envi,
                'uplist' =>$uplist,
                'envisavenote' => $envisavenote

            ];
    }


    public function savedata (Request $request){

        $data_all = request()->all();
        // $splist = $request->session()->get('splist');
        $data=$data_all['data'];
        $entry=$data_all['entry'];
        $user=$data_all['user'];
        $test='';
        $plotType=$data_all['plotType'];

        $datasavenote='';
        if ($plotType=='ss10m'){
            $table = $this->get10mDataTableInstance($entry);
        } else {
            $table = $this->get1haDataTableInstance($entry);
        }
        
        

        for($i=0; $i<count($data);$i++){
            if ($plotType=='ss1ha'){
                $data[$i]['stemid']=$data[$i]['tag'].".".$data[$i]['branch'];
            }
            $uplist=[];
            $datacheck=['pass'=>'1', 'datasavenote'=>''];
            if ($data[$i]['date']==''){$data[$i]['date']='0000-00-00';}
            $data[$i]['code']=strtoupper($data[$i]['code']);  //轉為皆大寫
            $check = new SsPlotDataCheck;
            $datacheck=$check->check($data[$i], $plotType, $entry);
            $data[$i]=$datacheck['data'];

            if ($datacheck['pass']==1){

                $odata=$table::where('stemid', 'like', $data[$i]['stemid'])->get()->toArray();
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
        if ( $plotType=='ss10m'){
            $redata=$this->get10mRedata($entry, $data[0]['plot'], $data[0]['sqx'], $data[0]['sqy'], $user);
        } else {
            $redata=$this->get1haRedata($entry, $data[0]['qx'], $data[0]['qy'], $data[0]['sqx'], $data[0]['sqy'], $user);
        }

        

            return [
                'result' => 'ok',
                'data' => $redata,
                // 'codea' => $uplist,
                // 'datacheck' => $datacheck,
                'datasavenote' => $datasavenote

            ];
    }


    public function saverecruit(Request $request){
        // $splist = $request->session()->get('splist');
        $data_all = request()->all();

        $data=$data_all['data'];
        $entry=$data_all['entry'];
        $user=$data_all['user'];
        $thispage=$data_all['thispage'];
        $plotType=$data_all['plotType'];
        $recruitsavenote='';
        $datacheck='';
        $uplistalter='';
        $q=0;
        $nonsavelist=[];
        $sqx='';
        $sqy='';

        if ($plotType=='ss10m'){
            $table = $this->get10mDataTableInstance($entry);
            $includeKeys = ['plot', 'sqx', 'sqy', 'csp'];
        } else {
            $table = $this->get1haDataTableInstance($entry);
            $includeKeys = ['qx','qy', 'sqx', 'sqy', 'csp'];
        }

        // $table = $this->get10mDataTableInstance($entry);

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
                $excludedKeys = ['ill','leave','code', 'tofix', 'note', 'status'];
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
 
            if ($plotType=='ss10m'){
                $data[$i]['tag']=str_pad($data[$i]['tag'],3,'0',STR_PAD_LEFT);  //在左側補0;
                
                $data[$i]['stemid']=$data[$i]['plot']."-".$data[$i]['tag'].".".$data[$i]['branch'];
            } else {
                $data[$i]['stemid']=$data[$i]['tag'].".".$data[$i]['branch'];
            }

            $data[$i]['code']=strtoupper($data[$i]['code']);
            $data[$i]['status']='-9';
            
            
            $datacheck=['pass'=>'1', 'datasavenote'=>''];

            $check = new SsPlotRecruitCheck;
            $datacheck=$check->check($data[$i], $entry, $plotType);
            // $datacheck['pass']=0;
            if ($datacheck['pass']==1){


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

                    // $data[$i]['spcode']=$temp[0];


                    foreach($data[$i] as $key => $value){
                        $excludedKeysall=['update_id', 'updated_at', 'alternotetable'];
                        if (!in_array($key, $excludedKeysall)){
                            if ($odata[0][$key] != $value){
                                if($value==Null){$value='';}

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

                        $uplist['update_id']=$user;

                        $table::where('stemid', 'like', $data[$i]['stemid'])->update($uplist);

                    }

                } else {
// //如果是新增樹

                    // $data[$i]['spcode']=$temp[0];
                    // $data[$i]['status']='-9';
                    // $data2=$data[$i];
                //新增
                    foreach($data[$i] as $key => $value){
                        $inlist[$key]=$value;
                    }
                    $inlist2=$inlist;
                    $table::insert($inlist);
                    
                }

                $recruitsavenote=$recruitsavenote."<br>第".($i+1).'筆資料已儲存';
                    $nonsavelist[$i]['date']='';
                    $nonsavelist[$i]['sqx']='';
                    $nonsavelist[$i]['sqy']='';
                    $nonsavelist[$i]['tag']='';
                    $nonsavelist[$i]['branch']='0';
                    $nonsavelist[$i]['csp']='';
                    $nonsavelist[$i]['code']='';
                    $nonsavelist[$i]['dbh']='';
                    $nonsavelist[$i]['ill']='';
                    $nonsavelist[$i]['leave']='';
                    $nonsavelist[$i]['pom']='';
                    $nonsavelist[$i]['note']='';
                    $nonsavelist[$i]['tofix']='';
                    if ($plotType=='ss10m'){
                        $nonsavelist[$i]['plot']=$data[$i]['plot'];
                    } else {
                        $nonsavelist[$i]['qx']=$data[$i]['qx'];
                        $nonsavelist[$i]['qy']=$data[$i]['qy'];
                    }


            } else {  // $datacheck['pass']!=1
                $recruitsavenote=$recruitsavenote."<br>".$datacheck['datasavenote'];
                $nonsavelist[$i]=$data[$i];
                // break;
            }
        }//最外層


//         //重新載入資料
        if($sqx!=''){
            if ($plotType=='ss10m'){
                $redata=$this->get10mRedata($entry, $data[0]['plot'], $sqx, $sqy, $user);
            } else {
                $redata=$this->get1haRedata($entry, $data[0]['qx'],$data[0]['qy'], $sqx, $sqy, $user);
            }

        } else {
            $redata=[];
        }

        return [
            'result' => 'ok',
            'data' => $redata,
            'odata' => $data,
            'nonsavelist' => $nonsavelist,
            'thispage' => $thispage,
            // 'q'=>$q,
            // 'uplistalter' => $uplistalter,
            // 'pass' => $inlist2,
            'entry' => $entry,
            // 'test' => $arr3,
            'recruitsavenote' => $recruitsavenote

        ];

    }


    public function deletedata(Request $request, $stemid, $entry, $plotType, $thispage){
        $test='';
            $user = $request->session()->get('user', function () {
                return view('login1', [
                'check' => 'no'
                ]);
            });
        // $user='chialing';
        $datasavenote='';

        if ($plotType=='ss10m'){
            $table = $this->get10mDataTableInstance($entry);
        } else {
            $table = $this->get1haDataTableInstance($entry);
        }

          
            $thisdata=$table::where('stemid','like', $stemid)->get();
            if ($plotType=='ss10m'){
                $thisplot=$thisdata[0]['plot'];
            } else {
                $thisqx=$thisdata[0]['qx'];
                $thisqy=$thisdata[0]['qy'];
            }
            
            $thissqx=$thisdata[0]['sqx'];
            $thissqy=$thisdata[0]['sqy'];

            $d_record = $table::where('stemid', $stemid)->delete();


            $datasavenote='已刪除 '.$stemid.' 新增樹資料';

            if ($plotType=='ss10m'){
                $redata=$this->get10mRedata($entry, $thisplot, $thissqx, $thissqy, $user);
            } else {
                $redata=$this->get1haRedata($entry, $thisqx, $thisqy, $thissqx, $thissqy, $user);
            }
            

            return [
                'result' => 'ok',
                // 'user' => $user,
                // 'test'=> $test,
                'thispage' => $thispage,
                'recruit' => $redata,

                'datasavenote' => $datasavenote
            ];
    }

    public function savealternote(Request $request){


        $data_all = request()->all();
        $data=$data_all['data'][0];
        $entry=$data_all['entry'];
        $user=$data_all['user'];
        $plotType=$data_all['plotType'];
        $thispage=$data_all['thispage'];
        $inlist=[];
        $datasavenote='';

        $data2 = array_filter($data);
        unset($data2['stemid']);
        if ($plotType=='ss10m'){
            $table = $this->get10mDataTableInstance($entry);
        } else {
            $table = $this->get1haDataTableInstance($entry);
        }
        if (!empty($data2)){

            // 轉換為 JSON 字串
            $alterdata = json_encode($data2, JSON_UNESCAPED_UNICODE);


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

        if ( $plotType=='ss10m'){
            $redata=$this->get10mRedata($entry, $site[0]['plot'], $site[0]['sqx'], $site[0]['sqy'], $user);
        } else {
            $redata=$this->get1haRedata($entry, $site[0]['qx'], $site[0]['qy'], $site[0]['sqx'], $site[0]['sqy'], $user);
        }



        return [
            'result' => 'ok',
            'thispage' => $thispage,
            'data' => $redata,
            'datasavenote' => '資料已儲存'
            // 'inlist'=>$sql
        ];        

    }

    public function deletealter(Request $request, $stemid, $entry, $plotType, $thispage){

        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });

        if ($plotType=='ss10m'){
            $table = $this->get10mDataTableInstance($entry);
        } else {
            $table = $this->get1haDataTableInstance($entry);
        }

        $datasavenote='';
       

            
        $table::where('stemid', 'like', $stemid)->update(['alternote'=>'']);
        //         // $test='y';

        $datasavenote='已刪除 '.$stemid.' 特殊修改資料';


        // // 重新載入資料

        $site=$table::where('stemid', 'like', $stemid)->get();


        if ( $plotType=='ss10m'){
            $redata=$this->get10mRedata($entry, $site[0]['plot'], $site[0]['sqx'], $site[0]['sqy'], $user);
            $realterdata=['stemid'=>$stemid, 'plot'=>'', 'sqx'=>'', 'sqy' => '', 'tag'=>'', 'b'=>'', 'csp'=>''];
        } else {
            $redata=$this->get1haRedata($entry, $site[0]['qx'], $site[0]['qy'], $site[0]['sqx'], $site[0]['sqy'], $user);
            $realterdata=['stemid'=>$stemid, 'qx'=>'', 'qy'=>'','sqx'=>'', 'sqy' => '', 'tag'=>'', 'b'=>'', 'csp'=>''];
        }


        $havedata='no';

        return [
            'result' => 'ok',
            'thispage' => $thispage,
            'data' => $redata,
            'realterdata' => $realterdata,
            'havedata' => $havedata,

            'datasavenote' => $datasavenote
        ];


    }


    public function saveaddcov (Request $request){
        $data_all = request()->all();
        // $splist = $request->session()->get('splist');
        $data=$data_all['data'];
        $entry=$data_all['entry'];
        $user=$data_all['user'];

        $nonsavelist=[];
        $addcovsavenote='';

        // // $test='';

        $sqx='0';
        $sqy='0';


        $table = $this->get10mCovTableInstance($entry);

        for($i=0; $i<count($data);$i++){
            $pass='1';
            $inlist=[];
            $uplistalter=[];
            $uplist=[];

            if (is_null($data[$i]['date']) || $data[$i]['date']==''){
          
                $pass = '0';
                $nonsavelist[$i]=$data[$i];
                continue;  //跳過這筆資料

            }

            foreach ($data[$i] as $key => $value){
                $excludedKeys = ['note', 'height'];
                if (!in_array($key, $excludedKeys) && is_null($value)) {
                    $pass = '0';
                    $addcovsavenote = $addcovsavenote."<br> 第".($i+1)."筆 "." ". $key.'資料不全，不予處理。';
                    $nonsavelist[$i]=$data[$i]; break;
                } 
            }

            if ($pass == '0'){continue;}
            

               if($data[$i]['layer']=='u'){
                    if ($data[$i]['height']=='0' || is_null($data[$i]['height'])){
                        $pass='0';
                        $addcovsavenote .= "<br>第".($i+1)."筆 資料 缺少高度值";
                    }
               } else if($data[$i]['layer']=='o'){
                    if (is_null($data[$i]['height'])){
                        $data[$i]['height']='0';
                    }
                    else if ($data[$i]['height']!='0'){
                        $pass='0';
                        $addcovsavenote.= "<br>第".($i+1)."筆 資料 高度應為 0";
                    } 
                }


            // 看是否有重複資料
                $covtables = $table::where([
                    ['plot', 'like', $data[$i]['plot']],
                    ['sqx', 'like', $data[$i]['sqx']],
                    ['sqy', 'like', $data[$i]['sqy']],
                    ['layer', 'like', $data[$i]['layer']],
                    ['csp', 'like', $data[$i]['csp']]
                ])->get();

                if(count($covtables)>0){
                        $pass='0';
                        $addcovsavenote.= "<br>第".($i+1)."筆 資料 重複輸入";
                }



            if ($pass=='1'){


                $data[$i]['update_id']=$user;
                $data[$i]['updated_at']=date("Y-m-d H:i:s");
                $data[$i]['id']='0';
                if (is_null($data[$i]['note'])){$data[$i]['note']='';}



                //新增
                    foreach($data[$i] as $key => $value){
                        $inlist[$key]=$value;
                    }
                    $inlist2=$inlist;
                    $table::insert($inlist2);



                
                $addcovsavenote=$addcovsavenote."<br>第".($i+1).'筆資料已儲存';
                    $nonsavelist[$i]['date']='';
                    $nonsavelist[$i]['plot']=$data[$i]['plot'];
                    $nonsavelist[$i]['sqx']='';
                    $nonsavelist[$i]['sqy']='';
                    $nonsavelist[$i]['layer']='';
                    $nonsavelist[$i]['csp']='';
                    $nonsavelist[$i]['cover']='';
                    $nonsavelist[$i]['height']='';
                    $nonsavelist[$i]['note']='';

                if($sqx=='0'){$sqx=$data[$i]['sqx']; $sqy=$data[$i]['sqy'];}

            } else {  // $datacheck['pass']!=1
                
                $nonsavelist[$i]=$data[$i];
                // break;

            }
        }//最外層

//         //重新載入資料
        if($sqx=='0'){$sqx='1'; $sqy='1';}

            $redata=$this->get10mRecovdata($entry, $data[0]['plot'], $sqx, $sqy, $user);

        return [
            'result' => 'ok',
            'data' => $redata,
            'odata' => $data,
            'nonsavelist' => $nonsavelist,
            // 'covtables' => $covtables,
            // 'sqy' => $data[0]['sqy'],
            // 'q'=>$q,
            // 'uplistalter' => $uplistalter,
            // 'inlist' => $inlist2,
            // 'entry' => $entry,
            // 'test' => $arr3,
            'addcovsavenote' => $addcovsavenote

        ];

    }

    public function deletecov(Request $request, $id, $entry){
        $test='';
            $user = $request->session()->get('user', function () {
                return view('login1', [
                'check' => 'no'
                ]);
            });
        // $user='chialing';
        $covsavenote='';

        $table = $this->get10mCovTableInstance($entry);

          
            $thisdata=$table::where('id','like', $id)->get();
            $thisplot=$thisdata[0]['plot'];
            $thissqx=$thisdata[0]['sqx'];
            $thissqy=$thisdata[0]['sqy'];
            // $total=$table::where('plot', 'like', $thisplot)->where('sqx', 'like', $thissqx)->where('sqy', 'like', $thissqy)->orderBy('stemid', 'asc')->get();

            $d_record = $table::where('id','like',$id)->delete();

                // $test='y';

            $covsavenote='已刪除 '.$thisdata[0]['csp'] .' 覆蓋度資料';

            $redata=$this->get10mRecovdata($entry, $thisplot, $thissqx, $thissqy, $user);

            return [
                'result' => 'ok',
                // 'user' => $user,
                // 'test'=> $test,
                // 'thispage' => $thispage,
                'covs' => $redata,

                'covsavenote' => $covsavenote
            ];
    }


    public function savecov (Request $request){
        $data_all = request()->all();
        // $splist = $request->session()->get('splist');
        $data=$data_all['data'];
        $entry=$data_all['entry'];
        $user=$data_all['user'];


        $covsavenote='';

        $table = $this->get10mCovTableInstance($entry);

        for($i=0; $i<count($data);$i++){
         
            $uplist=[];

               if($data[$i]['layer']=='u'){
                    if ($data[$i]['height']=='0'){
                       
                        $covsavenote = "第".($i+1)."筆 資料 缺少高度值";
                        break;
                    }
               } else if($data[$i]['layer']=='o'){
                    if ($data[$i]['height']!='0'){
                       
                        $covsavenote = "第".($i+1)."筆 資料 高度應為 0";
                        break;
                    }
               }

            // 看是否有重複資料


            //找舊資料
            $ocov=$table::query()->where('id', 'like', $data[$i]['id'])->get()->toArray();
            foreach($data[$i] as $key => $value){
                $excludedKeys=['update_id', 'updated_at','delete'];
                if (!in_array($key, $excludedKeys)){
                    if ($ocov[0][$key] != $value){
                        
                        if($value==Null){$value='';}
                        $uplist[$key]=$value;
                    }

                }
            }
            $covtables=[];
            if ($uplist!=[]){

                $covtables = $table::where([
                    ['plot', 'like', $data[$i]['plot']],
                    ['sqx', 'like', $data[$i]['sqx']],
                    ['sqy', 'like', $data[$i]['sqy']],
                    ['layer', 'like', $data[$i]['layer']],
                    ['csp', 'like', $data[$i]['csp']],
                    ['id', '!=', $data[$i]['id']]
                ])->get();

                if(count($covtables)>0){
                        $pass='0';
                        $covsavenote = "第".($i+1)."筆 資料 重複種類";
                } else {

                    $uplist['update_id']=$user;
                    $table::where('id', 'like', $data[$i]['id'])->update($uplist);
                    $covsavenote='資料已儲存';

                }

            }
        
        }//最外層

// //         //重新載入資料

            $redata=$this->get10mRecovdata($entry, $data[0]['plot'], $data[0]['sqx'], $data[0]['sqy'], $user);

        return [
            'result' => 'ok',
            'data' => $redata,
            'odata' => $data,
            'covsavenote' => $covsavenote

        ];

    }


//後端資料更正

    public function saveupdate (Request $request){

        $data_all = request()->all();
        $splist=[];

        $splists=SsSplist::select('spcode', 'index')->get()->toArray();
        foreach($splists as $splist1){
            $splist[$splist1['spcode']]=$splist1['index'];
        }

        $user=$data_all['user'];
        $from=$data_all['from'];
        $plotType=$data_all['plotType'];
        $base=$data_all['data1'][0];  

        if ($plotType=='ss1ha'){
            $tablebase = new Ss1haBase2024;
            $tablebaser = new Ss1haBaseR2024;
            $tablecensus = new Ss1haData2024;
            $tablefixlog = new SsFixlog;
        } 


//         $test='';
        $datasavenote='';

//base
        $ob_updateBase = new TreeUpdateBase;
        $result=$ob_updateBase->up($plotType, $data_all, $splist);
        $datasavenote=$result['datasavenote'];
        $thisstemid=$result['thisstemid'];
        $ostemid=$result['ostemid'];
        $newstemid=$result['newstemid'];
        $pass=$result['pass'];
// $thisstemid='';
// $pass=0;
        if ($pass=='1'){

            $stemidlist=[];
            
// //census
            for ($i = 1; $i <= 2; $i++) {
                $lastcensus=2;
                ${"census$i"} = $data_all['data2'][$i-1];
                if ($newstemid!=$ostemid){
                    ${"census$i"}['stemid']=$newstemid;
                    ${"census$i"}['tag']=$base['tag'];
                    ${"census$i"}['branch']=$base['branch'];
                }


                switch ($i) {
                    case 1:$table= new Ss1haData2015; break;
                    case 2:$table= new Ss1haData2024; break;
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
        $keylist=$result2['keylist'];

            }
        }

        


            return [
                'result' => 'ok',
                'thisstemid' => $thisstemid,
                'baser_insert'=>$result,
                // 'codetemp' => $ocensus1,
                'from'=> $from,
                // 'keylist' => $keylist,
                'datasavenote' => $datasavenote

            ];
    }


    public function deleteCensusData(Request $request){

        $data_all = request()->all();

        $user=$data_all['user'];
        $from=$data_all['from'];
        $stemid=$data_all['stemid'];
        $plotType=$data_all['plotType'];

        if ($plotType=='ss1ha'){
            $tablebase = new Ss1haBase2024;
            $tablebaser = new Ss1haBaseR2024;
            $tablecensus = new Ss1haData2024;
            $tablefixlog = new SsFixlog;

            $baseSheet='1ha_base_2024';
            $baseRSheet='1ha_base_r_2024';

        } 

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

        if ($b!='0'){  //分支

            for ($j = 1; $j <= 2; $j++) {

                $temp=[];
                if ($plotType=='ss1ha'){
                    switch ($j) {
                        case '1':$table= new Ss1haData2015; $censusSheet='1ha_data_2015'; break;
                        case '2':$table= new Ss1haData2024; $censusSheet='1ha_data_2024'; break;
                    }
                }
                
                $temp = $table::where('stemid', 'like', $stemid)->get()->toArray();

                if (count($temp)>0){

                    $table::where('stemid','like', $stemid)->delete();
                    $datasavenote='已刪除 '.$stemid.' 資料';
                    $fixlog['id']='0';
                    $fixlog['from']=$from;
                    $fixlog['type']='delete';
                    $fixlog['sheet']=$censusSheet;
                    $fixlog['qx']=substr($stemid, 0, 2);
                    $fixlog['stemid']=$stemid;
                    $fixlog['descript']='刪除此編號資料';
                    $fixlog['update_id']=$user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                    $tablefixlog::insert($fixlog);
                    $thisstemid=$stemid;
                } 

            }
        } else {

            $uplist['deleted_at']=date("Y-m-d H:i:s");
            $uplist['update_id']=$user;

            $tablebase::where('tag', 'like', $tag)->update($uplist);
                    $fixlog['id']='0';
                    $fixlog['from']=$from;
                    $fixlog['type']='delete';
                    $fixlog['sheet']=$baseSheet;
                    $fixlog['qx']=substr($stemid, 0, 2);
                    $fixlog['stemid']=$tag;
                    $fixlog['descript']='軟刪除此編號base資料';
                    $fixlog['update_id']=$user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                    $tablefixlog::insert($fixlog);
                    // $fixall[]=$fixlog;
            $baser=$tablebaser::where('stemid', 'like', $stemid)->get()->toArray();
            if (count($baser)>0){
                $tablebaser::where('stemid', 'like', $stemid)->update($uplist);
                    $fixlog['sheet']=$baseSheetR;
                    $tablefixlog::insert($fixlog);
            }

            
                    

            for ($j = 1; $j <= 2; $j++) {

                $temp=[];
                $fixlog=[];
                if ($plotType=='ss1ha'){
                    switch ($j) {
                        case '1':$table= new Ss1haData2015; $censusSheet='1ha_data_2015'; break;
                        case '2':$table= new Ss1haData2024; $censusSheet='1ha_data_2024'; break;
                    }
                }
                
                $temp = $table::where('tag', 'like', $tag)->get()->toArray();

                if (count($temp)>0){

                    $table::where('tag', 'like', $tag)->update($uplist);
                    $datasavenote='已刪除 '.$tag.' 所有資料';
                    $fixlog['id']='0';
                    $fixlog['from']=$from;
                    $fixlog['type']='delete';
                    $fixlog['sheet']=$censusSheet;
                    $fixlog['qx']=substr($stemid, 0, 2);
                    $fixlog['stemid']=$tag;
                    $fixlog['descript']='軟刪除此編號所有植株資料';
                    $fixlog['update_id']=$user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");
                    // $fixall[]=$fixlog;
                    $tablefixlog::insert($fixlog);
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


}

