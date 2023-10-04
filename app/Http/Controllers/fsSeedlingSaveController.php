<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingBase;
use App\Models\FsSeedlingCov;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;

use App\Jobs\fsSeedlingAddButton;

class fsSeedlingSaveController extends Controller
{

     public function getTableInstance($entry) {
        if ($entry == '1') {
            return new FsSeedlingSlrecord1;
        } else {
            return new FsSeedlingSlrecord2;
        }
    }

    public function getTableInstanceCov($entry) {
        if ($entry == '1') {
            return new FsSeedlingSlcov1;
        } else {
            return new FsSeedlingSlcov2;
        }
    }

    public function getTableInstanceRoll($entry) {
        if ($entry == '1') {
            return new FsSeedlingSlroll1;
        } else {
            return new FsSeedlingSlroll2;
        }
    }

    public function finishnote(Request $request, $entry){
        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });


        $tablecov = $this->getTableInstanceCov($entry);
        $table = $this->getTableInstance($entry);
        $pass='1';
        $finishnote='';
        $cov = $tablecov::query()->where('date', 'like', '0000-00-00')->get();
        if (count($cov)!='0'){
            foreach($cov as $temp){
                $traplist[]=$temp['trap'];
            }
            $traplist=array_unique($traplist);
            sort($traplist);
            $string = implode(", ", $traplist);
            $finishnote='有資料未輸入完成 ['.$string.']';
        } else {


            $data = $table::query()->where('date', 'like', '0000-00-00')->get();
            if (count($data)!='0'){
                foreach($data as $temp){
                    $traplist[]=$temp['trap'];
                }
                $traplist=array_unique($traplist);
                sort($traplist);
                $string = implode(", ", $traplist);
                $finishnote='有資料未輸入完成 ['.$string.']';
            }

        }

        if ($finishnote==''){$finishnote='輸入完成';}

        // echo $user;
        return [
            'result' => 'ok',
            'pass' => $pass,
            // 'test'=> $splist,

            'finishnote' => $finishnote
        ];

    }

    public function savecov(Request $request){

        $data_all = request()->all();
        // print_r($savecov);
        $savecov=$data_all['data'];
        $entry=$data_all['entry'];
        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });

        $covsavenote='';

        $tablecov = $this->getTableInstanceCov($entry);

        for($i=0; $i<count($savecov);$i++){

            if ($savecov[$i]['date']=='0000-00-00'){
                $covsavenote='需有日期資料';
                break;
            }

             if ($savecov[$i]['canopy']=='' || $savecov[$i]['date']=='' || $savecov[$i]['cov'] == ''){
                $covsavenote='資料有空白值';
                break;
            }           

            if ($savecov[$i]['cov']<0 || $savecov[$i]['cov']>100){
                $covsavenote='覆蓋度資料有誤';
                break;

            } else {

               
                $tablecov::where('id', $savecov[$i]['id'])->update(['cov'=>$savecov[$i]['cov'], 'date'=>$savecov[$i]['date'], 'canopy'=>$savecov[$i]['canopy'], 'note'=>$savecov[$i]['note'], 'update_id'=>$user]);
                    //重新下載資料


                $covsavenote='已儲存環境資料';
            }
        }


            return [
                'result' => 'ok',
                // 'covs' => $slcov,
                'covsavenote' => $covsavenote

            ];
        
    }


 public function savedata(Request $request){

        $data_all = request()->all();
        // // print_r($savecov);
        $data=$data_all['data'];
        $entry=$data_all['entry'];
        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        // $user=$data[0]['user'];
        // // $temp=[];
        // $list='';
        $datasavenote='';

        $table = $this->getTableInstance($entry);

        for ($i=0;$i<count($data);$i++){
            // $list[]=$data[$i]['tag'];
            $uplist=[];
//需有資料
            if ($data[$i]['date']=='0000-00-00' || $data[$i]['ht']=='' || $data[$i]['leafno']==''){
                $datasavenote=$data[$i]['tag'].' 需有日期/長度/葉片數 資料';
                break;
            }
            if ($data[$i]['sprout']=='TRUE'){///sprout

                $sprout_tag=explode('.',trim($data[$i]['tag']));

                $slrecord=$table::where('mtag', 'like', $sprout_tag[0])->where('sprout', 'like', 'FALSE')->get();

                if (!$slrecord->isEmpty()){

                    if ($data[$i]['ht']=='-4' && $slrecord[0]['ht']!='-4'){
                        $datasavenote=$data[$i]['tag']." 植株存活但萌櫱苗死亡，萌櫱苗記為 -7, status 為 A";
                    break;
                    }

                    if ($data[$i]['ht']=='-6' && $slrecord[0]['ht']!='-6'){
                        $datasavenote=$data[$i]['tag']." 植株存活但萌櫱苗消失，萌櫱苗記為 -7, status 為 A";
                    break;
                    }

                    if ($data[$i]['status']!=$slrecord[0]['status']){
                        $datasavenote=$data[$i]['tag']." 萌櫱苗的 status 需與主幹相同 (若該植株尚有分支存活，status 皆為 A)";
                    break;
                    }

                    if ($data[$i]['csp']!=$slrecord[0]['csp']){
                        $datasavenote=$data[$i]['tag']." 萌櫱苗的 csp 需與主幹相同 ";
                    break;
                    }

                    if ($data[$i]['trap']!=$slrecord[0]['trap'] || $data[$i]['plot']!=$slrecord[0]['plot']){
                        $datasavenote=$data[$i]['tag']." 萌櫱苗的樣區位置需與主幹相同 ";
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
// $list=$list.$data[$i]['tag']."+31";
                break;

                } else if ($data[$i]['ht']=='-1' && $data[$i]['status']!='A'){
                $datasavenote=$data[$i]['tag']." 長度 = -1，狀態欄需為 A";
                break;
                // } else if ($data[$i]['ht']=='-2' && $data[$i]['status']!='L'){
                //     $datasavenote=$data[$i]['tag']." 長度 = -2，狀態欄需為 L";
                //      ;
                //     break;
                } else if ($data[$i]['ht']=='-4'){
                    if ($data[$i]['status']!='G' && $data[$i]['status']!='D'){
                    $datasavenote=$data[$i]['tag']." 長度 = -4，狀態欄需為 G or D";
                    break;
                    }
                } else if ($data[$i]['ht']=='-6' && $data[$i]['status']!='N'){
                    $datasavenote=$data[$i]['tag']." 長度 = -6，狀態欄需為 N";
                    break;
                } else if ($data[$i]['ht']=='-7' && $data[$i]['status']!='A'){
                    $datasavenote=$data[$i]['tag']." 長度 = -7，狀態欄需為 A";
                    break;
                }
                
            } else if ($data[$i]['ht']==0){
                $datasavenote=$data[$i]['tag']." 長度 不得為 0";
                break;
            } else  if ($data[$i]['ht']>'0' && $data[$i]['status']!='A'){
                $datasavenote=$data[$i]['tag']." 長度 > 0，狀態欄需為 A";
                break;
            }

//子葉數
            if ($data[$i]['cotno']>2){
                $datasavenote=$data[$i]['tag']." 子葉數不得 > 2";
                break;
            }

            $slrecord=FsSeedlingData::where('tag', 'like', $data[$i]['tag'])->orderBy('id', 'DESC')->get();
            if (!$slrecord->isEmpty()){
                if ($slrecord[0]['cotno']>=0 && $data[$i]['cotno']> $slrecord[0]['cotno']){
                    $datasavenote=$data[$i]['tag']." 子葉數不得增加，如需修改增加和舊資料，請利用特殊修改";
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
                    break;
                }
            }

//修改tag  //如果是修改新增小苗的號碼，則mtag也要一起修改
            
            $slrecord=$table::where('id', 'like', $data[$i]['id'])->get();

            if ($data[$i]['tag'] != $slrecord[0]['tag']){
                $data[$i]['tag']=strtoupper($data[$i]['tag']);
                $mtag=explode('.',trim($data[$i]['tag']));
                $data[$i]['mtag'] = $mtag[0];
            }
//如果原本的status是N，後來不是N (A, G, D)，更新alternote
        //echo 'recruit: '.$data[$i]['recruit'];
            if ($slrecord[0]['status'] == 'N' && $data[$i]['status'] !='N'){

                if ($data[$i]['alternote']!=''){
                    $alterdata = json_decode($result[0]['alternote'], true);  //把json轉array
                }
                $alterdata['狀態']=$data[$i]['status'];

                $data[$i]['alternote'] = json_encode($alterdata, JSON_UNESCAPED_UNICODE);  //把array轉json


            }





            
// ['year' => date('Y'), 'month' => $month, 'date' => '0000-00-00']
            foreach($data[$i] as $key => $value){
                // dd($key);
                if (!in_array($key, ['user', 'entry', 'updated_at', 'update_id', 'alternotetable'])){
                    if ($slrecord[0][$key]!=$value){
                        $uplist[$key]=trim($value);

                    }
                }
            }
            // dd($uplist);
            // $uplist2="['update_id' => 'test']";
            if ($uplist!=[]){  //有資料要存
                $list=$data[$i]['tag'];
                $uplist['update_id'] =$user;

                $table::where('id', 'like', $data[$i]['id'])->update($uplist); 

                $datasavenote='資料已儲存';
            } else {
                // $datasavenote='未有需更新之資料';
            }
       
      } 
            return [
                'result' => 'ok',
                // 'uplist' => $uplist,
                'data' => $data,
                // 'list' => $list,
                'datasavenote' => $datasavenote

            ];
        
    }


    public function saverecruit(Request $request){

        $data = request()->all();
        // print_r($savecov);
        $recruit=$data['data'];
        $entry=$data['entry'];
        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        $recruitsavenote='';
        $nonsavelist=[];

        $table = $this->getTableInstance($entry);

        // $temp=[[]];

        for($i=0; $i<count($recruit);$i++){
        // $recruitsavenote='';
        $insertkey='';
        $insertvalue='';
        $insert1='';
        

            if ($recruit[$i]['date']=='' ) {
                // $recruitsavenote = '資料不完整';
                $nonsavelist[$i]=$recruit[$i]; 
                continue;
            }

            if ($recruit[$i]['tag']==''){
                $nonsavelist[$i]=$recruit[$i]; 
                continue;
            } else {
                $recruit[$i]['tag']=strtoupper($recruit[$i]['tag']); //轉為大寫

                if ($recruit[$i]['plot']=='' || $recruit[$i]['csp']=='' || $recruit[$i]['ht']=='' || $recruit[$i]['leafno']==''){
                    $recruitsavenote = $recruitsavenote."<br>第".($i+1).'筆資料 資料不完整';
                    $nonsavelist[$i]=$recruit[$i]; 
                    continue;
                }
                if ($recruit[$i]['cotno']==''){
                    $recruit[$i]['cotno']=0;
                }

//重號檢查
                $seedling=FsSeedlingdata::where('tag', 'like', $recruit[$i]['tag'])->get();
                if (!$seedling->isEmpty()){
                    $recruitsavenote = $recruitsavenote."<br>第".($i+1).'筆資料 重號';
                    $nonsavelist[$i]=$recruit[$i]; 
                    continue;
                } else {
                    
                    $seedling2=$table::where('tag', 'like', $recruit[$i]['tag'])->get();

                    if (!$seedling2->isEmpty()){
                        $recruitsavenote = $recruitsavenote."<br>第".($i+1).'筆資料 重號或已輸入';
                        $nonsavelist[$i]=$recruit[$i]; 
                        continue;
                    }                    
                }
                $mtag=explode('.', $recruit[$i]['tag']);
                $recruit[$i]['mtag']=$mtag[0];

                if (isset($mtag[1])){  //萌櫱
                    if ($recruit[$i]['sprout']=='FALSE'){
                        $recruitsavenote = $recruitsavenote."<br>第".($i+1).'筆資料 萌櫱狀態錯誤，請確認';
                        $nonsavelist[$i]=$recruit[$i]; 
                        continue;
                    }

                    if ($recruit[$i]['cotno']>0){
                        $recruitsavenote = $recruitsavenote."<br>第".($i+1).'筆資料 萌櫱苗不會有子葉，請確認';
                        $nonsavelist[$i]=$recruit[$i]; 
                        continue;
                    }
                }


                if ($recruit[$i]['ht']==0 ){
                    $recruitsavenote= $recruitsavenote."<br>第".($i+1).'筆資料 長度不得為 0';
                    $nonsavelist[$i]=$recruit[$i]; 
                    continue;
                } else if ($recruit[$i]['ht']<0){
                    if ($recruit[$i]['cotno']!=$recruit[$i]['ht'] || $recruit[$i]['leafno'] !=$recruit[$i]['ht']){
                        $recruitsavenote= $recruitsavenote."<br>第".($i+1).'筆資料 長度 < 0，子葉數與葉片數需與長度相同';
                        $nonsavelist[$i]=$recruit[$i]; 
                        continue;
                    }
                }

                 
                if ($recruit[$i]['cotno']>2){
                    $recruitsavenote= $recruitsavenote."<br>第".($i+1).'筆資料 子葉數不得 > 2';
                    $nonsavelist[$i]=$recruit[$i]; 
                    continue;
                }

                if ($recruit[$i]['sprout']=='TRUE'){
                    $recruit[$i]['x']='0';
                    $recruit[$i]['y']='0';

                    //找出是否有主幹資料

                    $sprout=$table::where('mtag', 'like', $recruit[$i]['mtag'])->where('sprout', 'like', 'FALSE')->get();

                    if (!$sprout->isEmpty()){
                        if ($sprout[0]['status']!='A'){
                            // $recruit[$i]['alternote']='主幹狀態不為A，請確認。';
                            $recruitsavenote=$recruitsavenote."<br>第".($i+1)."筆資料 主幹狀態不為 A，請確認。<br>";
                            $nonsavelist[$i]=$recruit[$i]; 
                            continue;

                        }

                        if ($recruit[$i]['csp']!=$sprout[0]['csp']){
                            $recruitsavenote=$recruitsavenote."<br>第".($i+1)."筆資料 萌櫱苗與主幹種類不同，請確認。<br>";
                            $nonsavelist[$i]=$recruit[$i]; 
                            continue; 
                        }

                        if ($recruit[$i]['trap']!=$sprout[0]['trap'] || $recruit[$i]['plot']!=$sprout[0]['plot']){
                            $recruitsavenote=$recruitsavenote."<br>第".($i+1)."筆資料 萌櫱苗與主幹的樣區位置不同，請確認。<br>";
                            $nonsavelist[$i]=$recruit[$i]; 
                            continue; 
                        }

                    } else {
                        // $recruit[$i]['alternote']='未有主幹資料，請確認。';
                        $recruitsavenote=$recruitsavenote."<br>第".($i+1)."筆資料 未有主幹資料，請確認。或請管理員加入資料。<br>";
                        $nonsavelist[$i]=$recruit[$i]; 
                        continue;
                    }

                }

                if ($recruit[$i]['x']==''){
                    if ($recruit[$i]['sprout']!='TRUE'){
                        $recruitsavenote= $recruitsavenote."<br>第".($i+1).'筆資料 需有座標';
                        $nonsavelist[$i]=$recruit[$i]; 
                        continue;
                    } else {
                        $recruit[$i]['x']='0';
                    }
                    
                } else if  ($recruit[$i]['x']>100 || $recruit[$i]['x']<0) {
                    $recruitsavenote= $recruitsavenote."<br>第".($i+1).'筆資料 座標不得 > 100 或 < 0';
                    $nonsavelist[$i]=$recruit[$i]; 
                    continue;
                }
                if ($recruit[$i]['y']==''){
                    if ($recruit[$i]['sprout']!='TRUE'){
                        $recruitsavenote= $recruitsavenote."<br>第".($i+1).'筆資料 需有座標';
                        $nonsavelist[$i]=$recruit[$i]; 
                        continue;
                    } else {
                        $recruit[$i]['y']='0';
                    }
                } else if  ($recruit[$i]['y']>100 || $recruit[$i]['y']<0) {
                    $recruitsavenote= $recruitsavenote."<br>第".($i+1).'筆資料 座標不得 > 100 或 < 0';
                    $nonsavelist[$i]=$recruit[$i]; 
                    continue;
                }

//補資料

                $census=$table::first();

                $recruit[$i]['census']=$census['census'];
                $recruit[$i]['year']=$census['year'];
                $recruit[$i]['month']=$census['month'];
                $recruit[$i]['status']='A';
                $recruit[$i]['id']='0';
                if (!isset( $recruit[$i]['note'])){
                $recruit[$i]['note']='';}
                if (!isset( $recruit[$i]['alternotetable'])){
                $recruit[$i]['alternote']='';} else {
                    $recruit[$i]['alternote']=$recruit[$i]['alternotetable'];
                }
                unset($recruit[$i]['alternotetable']);
                
                $recruit[$i]['update_id']=$user;
                $recruit[$i]['updated_at']=date("Y-m-d H:i:s");

                //存檔
                $insert2=[];

                foreach ($recruit[$i] as $key => $value){
                        $insert2[$key]=$value;
                        // $insertkey=$insertkey.$key.",";
                        // $insertvalue=$insertvalue."'".trim($value)."',";

                }
                $nonsavelist[$i]['date']='';
                $nonsavelist[$i]['trap']=$recruit[$i]['trap'];
                $nonsavelist[$i]['recruit']='R';
                $nonsavelist[$i]['sprout']='FALSE';
                $nonsavelist[$i]['tag']='';
                $nonsavelist[$i]['csp']='';
                $nonsavelist[$i]['ht']='';
                $nonsavelist[$i]['cotno']='';
                $nonsavelist[$i]['leafno']='';
                $nonsavelist[$i]['x']='';
                $nonsavelist[$i]['y']='';
                $nonsavelist[$i]['note']='';



                $table::insert($insert2);

                $recruitsavenote=$recruitsavenote."<br>第".($i+1).'筆資料已儲存';

            }  //來自 tag
        }

        //maxid
        $maxid=FsSeedlingSlrecord::count();

        //重新載入資料


        $redata=$table::where('trap', 'like', $recruit[0]['trap'])->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();
    

        $ob_redata = new fsSeedlingAddButton;
        $redata=$ob_redata->addbutton($redata, $entry);


        return [
            'result' => 'ok',
            // 'data' => $data,
            'recruit' => $redata,
            'maxid' => $maxid,
            'nonsavelist' => $nonsavelist,
            // 'temp' => $temp,
            'recruitsavenote' => $recruitsavenote
            // 'insert' => $insert1
           

        ];
        
    }

//可以增加thispage了，不用重算

    public function deletedata(Request $request, $tag, $entry, $thispage){
        $test='';
            $user = $request->session()->get('user', function () {
                return view('login1', [
                'check' => 'no'
                ]);
            });
        // $user='chialing';
        $datasavenote='';
    
        $table = $this->getTableInstance($entry);

                $trap=$table::where('tag','like', $tag)->get();
                $thistrap=$trap[0]['trap'];
                $total=$table::where('trap', 'like', $thistrap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();

                $d_record = $table::where('tag', 'like', $tag)->delete();

            $datasavenote='已刪除 '.$tag.' 新增小苗資料';
            $maxid=FsSeedlingSlrecord::count();

            // 重新載入資料


            $redata=$table::where('trap', 'like', $thistrap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();
                // $redata='1';


            $ob_redata = new fsSeedlingAddButton;
            $redata=$ob_redata->addbutton($redata, $entry);

            return [
                'result' => 'ok',
                // 'test'=> $test,
                'thispage' => $thispage,  
                'recruit' => $redata,
                'maxid' => $maxid,
                'datasavenote' => $datasavenote
            ];
    }


    public function saveslroll(Request $request, $entry, $trap){
        // $test='';
            $user = $request->session()->get('user', function () {
                return view('login1', [
                'check' => 'no'
                ]);
            });
        $tableroll = $this->getTableInstanceRoll($entry);
        $slrollsavenote='';
        $slrolldata = request()->all();
        $slrollnew=$slrolldata['data'];

        $insert1='';
        for($i=0;$i<count($slrollnew);$i++){
            $uplist=[];
            if (empty($slrollnew[$i])) break;


            if ($slrollnew[$i]['date']==''){
                break;
            }

            if ($slrollnew[$i]['trap']=='' ||$slrollnew[$i]['plot']=='' || $slrollnew[$i]['tag']==''){
                break;
            }

            if (isset($slrollnew[$i]['id'])){
                // 比對舊資料

                    $olddata=$tableroll::where('id', 'like', $slrollnew[$i]['id'])->get();

               foreach($slrollnew[$i] as $key => $value){
                    if ($key!='update_id' && $key !='updated_at' && $key!='delete'){
                        if ($olddata[0][$key]!=$value){
                            $uplist[$key]=trim($value);
                        }
                    }
               }


                if ($uplist!=[]){  //有資料要存
                    // $list=$data[$i]['tag'];
                    $uplist['update_id'] =$user;

                    $tableroll::where('id', 'like', $slrollnew[$i]['id'])->update($uplist); 

                    $slrollsavenote='資料已儲存';
                } 

            } else { //新資料
            $insertkey='';
            $insertvalue='';
            $insert2=[];
                $slrollnew[$i]['updated_at']=date("Y-m-d H:i:s");
                $cov=FsSeedlingSlcov1::first();
                // 存檔
                $slrollnew[$i]['month']=$cov['month'];
                $slrollnew[$i]['year']=$cov['year'];
                $slrollnew[$i]['id']='0';
               
                foreach ($slrollnew[$i] as $key => $value){
                    if ($key != 'delete' && $key !='update_id'){
                        $insertkey=$insertkey.$key.",";
                        $insertvalue=$insertvalue."'".trim($value)."',";
                        $insert2[$key]=$value;
                    }
                }


                $insertkey=$insertkey.'update_id';
                $insertvalue=$insertvalue."'".$user."'";
                $insert2['update_id']=$user;


                $tableroll::insert($insert2);

            $slrollsavenote='資料已儲存';

            }

 
        }

        // //重新載入資料

            $slroll=$tableroll::orderBy('trap', 'asc')->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();




            if (!$slroll->isEmpty()){
                $slroll=$slroll->toArray();
                for($m=0;$m<count($slroll);$m++){
                    $slroll[$m]['delete']="<button class='deleteroll' deleteid='".$slroll[$m]['id']."' tag='".$slroll[$m]['tag']."' entry='".$entry."' trap='".$trap."'>X</button>";
                }

            } else {
                $slroll=[];
            }

        // $y=count($slroll);
        // for($q=$y;$q<($y+10);$q++){
        //     $slroll[$q]['date']='0000-00-00';
        //     $slroll[$q]['trap']='';
        //     $slroll[$q]['plot']='';
        //     $slroll[$q]['tag']='';
        //     $slroll[$q]['year']=$slrollnew[0]['year'];
        //     $slroll[$q]['month']=$slrollnew[0]['month'];

        // }

        // $slroll[4]['trap']='';
        return [
            'result' => 'ok',
            'entry' => $entry,
            'data' => $slroll,
            'text' => $slrollnew,
            'trap' => $trap,
            'slrollsavenote' => $slrollsavenote

        ];

    }


    public function deleteslroll($tag, $id, $entry, $trap){
      
        $slrollsavenote='';
        $tableroll = $this->getTableInstanceRoll($entry);

            $tableroll::where('id', 'like', $id)->delete();
             
            $slrollsavenote='已刪除 '.$tag.' 撿到環資料';

            // 重新載入資料


                $slroll=$tableroll::orderBy('trap', 'asc')->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();


            if (!$slroll->isEmpty()){
                $slroll=$slroll->toArray();
                for($m=0;$m<count($slroll);$m++){
                    $slroll[$m]['delete']="<button class='deleteroll' deleteid='".$slroll[$m]['id']."' tag='".$slroll[$m]['tag']."' entry='".$entry."' trap='".$trap."'>X</button>";
                }

            } else {
                $slroll=[];
                $slroll[0]['year']='';
                $slroll[0]['month']='';
            }

        // $y=count($slroll);
        // for($q=$y;$q<($y+10);$q++){
        //     $slroll[$q]['date']='0000-00-00';
        //     $slroll[$q]['trap']='';
        //     $slroll[$q]['plot']='';
        //     $slroll[$q]['tag']='';
        //     $slroll[$q]['year']=$slroll[0]['year'];
        //     $slroll[$q]['month']=$slroll[0]['month'];

        // }

            return [
            'result' => 'ok',
            'data' => $slroll,
            'trap' => $trap,
            'slrollsavenote' => $slrollsavenote
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
        $uplist=[];

        //將data資料變為string
        $datasavenote='';
        $data2 = array_filter($data);
        unset($data2['id']);

        if (!empty($data2)){

            // 轉換為 JSON 字串
            $alterdata = json_encode($data2, JSON_UNESCAPED_UNICODE);

            $table = $this->getTableInstance($entry);
          
            $olddata=$table::where('id', 'like', $data['id'])->get()->toArray();

            if ($olddata[0]['alternote']!=$alterdata){
                $uplist['alternote']=$alterdata;
                $uplist['update_id']=$user;
                $table::where('id', 'like', $data['id'])->update($uplist);
            }
            $datasavenote='資料已儲存';
        }
 
//重新載入資料
            $maxid=FsSeedlingSlrecord::count();
        
            $redata=$table::where('trap', 'like', $olddata[0]['trap'])->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();
                // $redata='1';


            $ob_redata = new fsSeedlingAddButton;
            $redata=$ob_redata->addbutton($redata, $entry);        




        return [
            'result' => 'ok',
            'datasavenote' => $datasavenote,
            'data' => $redata,
            'maxid' => $maxid,
            'thispage' => $thispage
            // 'thispage' => $thispage
            // 'inlist'=>$sql
        ];        

    }

    public function deletealter(Request $request, $tag, $entry, $thispage){

        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        $table = $this->getTableInstance($entry);

        $datasavenote='';

        $table::where('tag', 'like', $tag)->update(['alternote'=>'']);
                // $test='y';

        $datasavenote='已刪除 '.$tag.' 特殊修改資料';


//重新載入資料
        $olddata=$table::where('tag', 'like', $tag)->get()->toArray();
        $maxid=FsSeedlingSlrecord::count();
        
        $redata=$table::where('trap', 'like', $olddata[0]['trap'])->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();
                // $redata='1';

        $ob_redata = new fsSeedlingAddButton;
        $redata=$ob_redata->addbutton($redata, $entry);  


        $realterdata=['Tag'=>'', 'Trap'=>'', 'Plot' => '', '原長度'=>'', '原葉片數'=>'', '狀態' => '', 'id'=> $olddata[0]['id']];
        $havedata='no';

        return [
            'result' => 'ok',
            // 'test'=> $test,
            'thispage' => $thispage,
            'data' => $redata,
            'maxid' => $maxid,
            'realterdata' => $realterdata,
            'havedata' => $havedata,

            'datasavenote' => $datasavenote
        ];


    }




}