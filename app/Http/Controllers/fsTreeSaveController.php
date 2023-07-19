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


            return [
                'result' => 'ok',
                'data' => $datacheck,
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

        $table = $this->getTableInstance($entry);

        for($i=0; $i<count($data);$i++){
            $pass='1';
            $inlist=[];
            $uplistalter=[];
            $uplist=[];

            if($data[$i]['date']==''){
                break;
            }

            foreach ($data[$i] as $key => $value){
                $excludedKeys = ['code', 'tofix', 'note'];
                if (!in_array($key, $excludedKeys) && $value=='') {
                    $pass = '0';
                    $recruitsavenote = $data[$i]['tag'] . $key.'資料不全，不予處理。';
                    break;  //離開檢查
                }
            }
            
            if ($pass=='0') {break;}   //離開資料迴圈

            $data[$i]['tag']=strtoupper($data[$i]['tag']);
            $data[$i]['code']=strtoupper($data[$i]['code']);
            $data[$i]['stemid']=$data[$i]['tag'].".".$data[$i]['branch'];
            
            
            // $datacheck=['pass'=>'1', 'datasavenote'=>''];

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
                $data[$i]['tocheck']='';
                $data[$i]['alternote']='';
                $data[$i]['show']='1';
                if ($data[$i]['tofix']==Null){$data[$i]['tofix']='';}
                if ($data[$i]['note']==Null){$data[$i]['note']='';}
 //如果是漏資料 
                if ($data[$i]['tofix']=='1'){
                    $data[$i]['status']='';
                    $odata=$table::where('stemid', 'like', $data[$i]['stemid'])->get();
                    $pass='1';

                    foreach($data[$i] as $key => $value){
                        $excludedKeysall=['update_id', 'updated_at', 'alternotetable'];
                        if (!in_array($key, $excludedKeysall)){
                            if ($odata[0][$key] != $value){
                                if($value==Null){$value='';}

                                $includeKeys = ['qx', 'qy', 'sqx', 'sqy', 'csp', 'pom', 'h1'];

                                if (in_array($key, $includeKeys)) {
                                    $uplistalter[$key] = $value;
                                    $recruitsavenote = $data[$i]['tag'] . ' 漏資料，但基本資料與原始資料不符。請確認編號，或洽管理員。';
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
                        break;
                    }

                    if ($uplist!=[]){

                        $uplist['update_id']=$user;

                        $table::where('stemid', 'like', $data[$i]['stemid'])->update($uplist);

                    }

                } else {
//如果是新增樹
// 8.1 新增樹種需要spcode
                    $temp=array_keys($splist,$data[$i]['csp']);
                    $data[$i]['spcode']=$temp[0];
                    $data[$i]['status']='-9';

                //新增
                    foreach($data[$i] as $key => $value){
                        $inlist[$key]=$value;
                    }

                    $table::insert($inlist);
                }


                $recruitsavenote='資料已儲存';

            } else {
                $recruitsavenote=$datacheck['datasavenote'];
                break;
                

            }
        }//最外層


//         //重新載入資料
        $redatas=$table::where('qx', 'like', $data[0]['qx'])->where('qy', 'like', $data[0]['qy'])->where('sqx', 'like', $data[0]['sqx'])->where('sqy', 'like', $data[0]['sqy'])->where('show', 'like', '1')->orderBy('stemid', 'asc')->get();


        $ob_redata = new fsTreeAddButton;
        $redata=$ob_redata->addbutton($redatas, $entry);


        return [
            'result' => 'ok',
            'data' => $redata,
            'odata' => $data,
            // 'uplistalter' => $uplistalter,
            // 'pass' => $inlist,
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
            $redatas=$table::where('qx', 'like', $thisqx)->where('qy', 'like', $thisqy)->where('sqx', 'like', $thissqx)->where('sqy', 'like', $thissqy)->where('show', 'like', '1')->orderBy('stemid', 'asc')->get();

            $ob_redata = new fsTreeAddButton;
            $redata=$ob_redata->addbutton($redatas, $entry);

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
        $redatas=$table::where('qx', 'like', $site[0]['qx'])->where('qy', 'like', $site[0]['qy'])->where('sqx', 'like', $site[0]['sqx'])->where('sqy', 'like', $site[0]['sqy'])->where('show', 'like', '1')->orderBy('stemid', 'asc')->get();
        // $redata='1';


        $ob_redata = new fsTreeAddButton;
        $redata=$ob_redata->addbutton($redatas, $entry);



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
        $redatas=$table::where('qx', 'like', $site[0]['qx'])->where('qy', 'like', $site[0]['qy'])->where('sqx', 'like', $site[0]['sqx'])->where('sqy', 'like', $site[0]['sqy'])->where('show', 'like', '1')->orderBy('stemid', 'asc')->get();

        $ob_redata = new fsTreeAddButton;
        $redata=$ob_redata->addbutton($redatas, $entry);

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

}

