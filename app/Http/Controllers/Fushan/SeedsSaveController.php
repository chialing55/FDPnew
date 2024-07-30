<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;
use App\Models\FsSeedsFixlog;

use App\Jobs\FsSeedsCheck;
use App\Jobs\SeedsAddButton;


class SeedsSaveController extends Controller
{


    public function getTableInstance($type) {
        if ($type == 'record') {
            return new FsSeedsRecord1;
        } else {
            return new FsSeedsFulldata;
        }
    }

//產生名錄
    public function spinfo(){
        $spinfolist=FsSeedsSplist::query()->get()->toArray();
        foreach ($spinfolist as $spinfo1){
            $spinfo[$spinfo1['csp']]=$spinfo1;
        }

        return $spinfo;
    }

//預設鑑定者
    public $identifier='蔡佳秀';

//重新載入資料
    public function getRedata(){

        $data1=FsSeedsRecord1::query()->get()->toArray();
        $ob_table = new SeedsAddButton;
        $redata=$ob_table->addbutton($data1, 'record');

        return $redata; 

    }
//重新載入資料2
    public function getRedata2($census){

        $data1=FsSeedsFulldata::where('census', 'like', $census)->get()->toArray();
        $ob_table = new SeedsAddButton;
        $redata=$ob_table->addbutton($data1, 'fulldata');

        return $redata; 

    }

//已輸入資料的修改與儲存
    public function savedata(Request $request, $type){

        $table = $this->getTableInstance($type);


        $data_all = request()->all();
        $data=$data_all['data'];
        $user = $data_all['user'];
        $datasavenote=''; 

        $spinfo=$this->spinfo();

        for ($i=0;$i<count($data);$i++){
            // $list[]=$data[$i]['tag'];
                $data[$i]['checknote']='';
                
                $checknote='';
                $data[$i]['trap'] = str_pad($data[$i]['trap'], 3, '0', STR_PAD_LEFT);
                $temp=[];
                $temp=$table::where('id','like',$data[$i]['id'])->get()->toArray();
                $exarray=['checknote', 'updated_at', 'updated_id'];
                $up='no';
                $updatedes=[];
                foreach($temp[0] as $key=>$value){
                    if(!in_array($key, $exarray)){
                        if($temp[0][$key]!=$data[$i][$key]){
                            $up='yes'; // break;
                            $updatedes[$key]=$temp[0][$key]."=>".$data[$i][$key];
                        }
                    }
                }

                if($up=='no') continue;

                $check = new FsSeedsCheck;
                // $checknote=$check->check($data[$i], $spinfo, 'o');
                $result=$check->check($data[$i], $spinfo, 'o', $type);
                $checknote=$result['checknote'];
                $data[$i]=$result['result'];

                $uplist=[];


                foreach ($data[$i] as $key => $value){
                    if ($value==Null){$value='';}
                    if ($key=='d'){continue;}

                    $uplist[$key] = $value;

                }
                $thisid=$data[$i]['id'];

                $uplist['checknote']=$checknote;
                $uplist['updated_id']=$user;

                $table::where('id', 'like', $data[$i]['id'])->update($uplist);
                $upfixlog=[];
                $datasavenote='已更新資料';
                if($updatedes!=[]){
                    $updatedes['id']=$data[$i]['id'];
                }
//若為大表的更新，需留下更新紀錄
                if ($type=='fulldata'){
                    $upfixlog['id']='0';
                    $upfixlog['type']='update';
                    $upfixlog['census']=$uplist['census'];
                    $upfixlog['descript']=json_encode($updatedes, JSON_UNESCAPED_UNICODE);
                    $upfixlog['updated_id']=$user;
                    $upfixlog['updated_at']=date("Y-m-d H:i:s");

                    FsSeedsFixlog::insert($upfixlog);

                } 
        } 


        if ($type=='record'){
            $redata=$this->getRedata();
            $thispage=ceil($thisid/29);            
        } else {
            $redata=$this->getRedata2($data[0]['census']);
            $k=($thisid-$redata[0]['id'])+1;
            $thispage=ceil($k/29);            
        }




            return [
                'result' => 'ok',
                'uplist' => $uplist,
                'data' => $redata,
                'thispage' => $thispage,
                // 'type2' => $result,
                'seedssavenote' => $datasavenote

            ];
        
    }
//空白表單的輸入與儲存
    public function savedata1(Request $request, $type){

        $table = $this->getTableInstance($type);

        $data_all = request()->all();
        // // print_r($savecov);
        $data=$data_all['data'];
        
        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        // $user=$data[0]['user'];
        // // $temp=[];
        // $list='';
        $checknote='';

        $spinfo=$this->spinfo();

        $inlist=[];

        for ($i=0;$i<count($data);$i++){
//             // $list[]=$data[$i]['tag'];
            if ($data[$i]['trap']!=''){
                if ($data[$i]['code']==''){
                    $data[$i]['code']='0';
                }
                if ($data[$i]['count']==''){
                    $data[$i]['count']='0';
                }
                $data[$i]['trap'] = str_pad($data[$i]['trap'], 3, '0', STR_PAD_LEFT);
                $check = new FsSeedsCheck;
                // $checknote=$check->check($data[$i], $spinfo, 'n');
                $result=$check->check($data[$i], $spinfo, 'n', $type);
                $checknote=$result['checknote'];
                $data[$i]=$result['result'];                // $checknote1.=$checknote;

                $inlist=[];
                foreach ($data[$i] as $key => $value){
                    if ($value==Null){$value='';}
                    if ($key=='id'){$value='0';}

                    $inlist[$key] = $value;
                }


                // if ($checknote==''){$inlist['pass']='y';} else {$inlist['pass']='n';}
                $inlist['checknote']=$checknote;
                $inlist['updated_id']=$user;
                $inlist['updated_at']=date("Y-m-d H:i:s");

                if ($type=='fulldata'){
                    $inlist['id']='0';
                    // $inlist['census']=$datacol['census'];
                        if (isset($spinfo[$inlist['csp']])){
                            $inlist['sp']=$spinfo[$inlist['csp']]['sp'];
                            $inlist['identified']=$spinfo[$inlist['csp']]['identified'];
                        } else {
                            $inlist['sp']='';
                            $inlist['identified']='N';
                        }

                }

                $table::insert($inlist);


                if ($type=='fulldata'){
                    $updatedes['trap']=$inlist['trap'];
                    $updatedes['csp']=$inlist['csp'];
                    $updatedes['code']=$inlist['code'];

                    $upfixlog['id']='0';
                    $upfixlog['type']='insert';
                    $upfixlog['census']=$inlist['census'];
                    $upfixlog['descript']=json_encode($updatedes, JSON_UNESCAPED_UNICODE);
                    $upfixlog['updated_id']=$user;
                    $upfixlog['updated_at']=date("Y-m-d H:i:s");

                    FsSeedsFixlog::insert($upfixlog);

                }

            }

        } 

        if ($type=='record'){
            $redata=$this->getRedata();
        
        } else {
            $redata=$this->getRedata2($data[0]['census']);
         
        }


        $emptytable=[];

        for($k=0;$k<29;$k++){
            $emptytable[$k]['id']=$k+1;
            $emptytable[$k]['census']=$redata[0]['census'];
            $emptytable[$k]['trap']='';
            $emptytable[$k]['csp']='';
            $emptytable[$k]['code']='';
            $emptytable[$k]['count']='';
            $emptytable[$k]['seeds']='';
            $emptytable[$k]['viability']='';
            $emptytable[$k]['fragments']='';
            $emptytable[$k]['sex']='';
            $emptytable[$k]['identifier']=$this->identifier;
            $emptytable[$k]['note']='';
        }

            return [
                'result' => 'ok',
                'inlist' => $inlist,
                'data' => $redata,
                'emptytable' => $emptytable,
                'seedssavenote' => '已新增資料'

            ];
        
    }

//刪除已輸入資料
    public function deletedata(Request $request, $id, $info, $thispage, $type){
        $test='';
            $user = $request->session()->get('user', function () {
                return view('login1', [
                'check' => 'no'
                ]);
            });
        // $user='chialing';
        $datasavenote='';

    
            $table = $this->getTableInstance($type);
            $census=$table::where('id', 'like', $id)->first()->toArray();
            $d_record = $table::where('id', 'like', $id)->delete();

            $datasavenote='已刪除 '.$info.' 種子雨資料';

            // 重新載入資料
            if ($type=='fulldata'){
                $updatedes['trap']=$census['trap'];
                $updatedes['csp']=$census['csp'];
                $updatedes['code']=$census['code'];

                $upfixlog['id']='0';
                $upfixlog['type']='delete';
                $upfixlog['census']=$census['census'];
                $upfixlog['descript']=json_encode($updatedes, JSON_UNESCAPED_UNICODE);
                $upfixlog['updated_id']=$user;
                $upfixlog['updated_at']=date("Y-m-d H:i:s");

                FsSeedsFixlog::insert($upfixlog);

            }

//更新data


        if ($type=='record'){
            $redata=$this->getRedata();
            $thispage=ceil($id/29);            
        } else {
            $redata=$this->getRedata2($census['census']);
            $k=($id-$redata[0]['id'])+1;
            $thispage=ceil($k/29);            
        }
        // $thispage=ceil($id/29);


        return [
            'result' => 'ok',
            // 'test'=> $test, 
            'data' => $redata,
            'thispage' => $thispage,
            'seedssavenote' => $datasavenote
        ];
    }

//輸入完成檢查，並匯入大表

    public function finishnote(Request $request){
        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });

        $finishnote='';
        $spinfo=$this->spinfo();
        $traps=[];

        $checkdata=FsSeedsRecord1::query()->where('checknote', 'not like', '')->get()->toArray();

        if (count($checkdata)>0){
            $finishnote='有資料錯誤未更正';
        } else {
            //匯入大表
            $datacol=FsSeedsRecord1::query()->first()->toArray();
            foreach ($datacol as $key => $value){
                $inlistf[$key]='';
            }
            $inlistf['id']='0';
            $inlistf['census']=$datacol['census'];
            // unset($inlistf['checknote']);
            // print_r($inlistf);
            // $traps=FsSeedsRecord1::select('trap')->groupBy('trap')->get()->toArray();

            for($j=1;$j<108;$j++){
                $trap = str_pad($j, 3, '0', STR_PAD_LEFT);

                $datas=FsSeedsRecord1::query()->where('trap', 'like',$trap)->get()->toArray();
                if (count($datas)>0){

                    for($i=0;$i<count($datas);$i++){

                        // unset($datas[$i]['checknote']);
                        $datas[$i]['id']='0';


                        $inlist=[];
                        foreach ($datas[$i] as $key => $value){
                            if ($value==Null){$value='';}
                            $inlist[$key] = $value;
                        }

                        if (isset($spinfo[$inlist['csp']])){
                            $inlist['sp']=$spinfo[$inlist['csp']]['sp'];
                            $inlist['identified']=$spinfo[$inlist['csp']]['identified'];
                        } else {
                            $inlist['sp']='';
                            $inlist['identified']='N';
                        }
                        $inlist['updated_id']=$user;
                        $inlist['updated_at']=date("Y-m-d H:i:s");
                        // print_r($inlist);
                        // echo "<br>";
                        FsSeedsFulldata::insert($inlist);
                    }

                } else {
                    $inlist=$inlistf;
                    $inlist['trap']=$trap;
                    $inlist['csp']='nothing';
                    $inlist['sp']='NOTHING';
                    $inlist['identified']='Y';
                    $inlist['code']='0';
                    $inlist['count']='0';
                    $inlist['seeds']='0';
                    $inlist['viability']='0';
                    $inlist['fragments']='0';
                    $inlist['sex']='';
                    $inlist['identifier']=$this->identifier;
                    $inlist['note']='';
                    $inlist['checknote']='';
                    $inlist['updated_id']=$user;
                    $inlist['updated_at']=date("Y-m-d H:i:s");
                    // print_r($inlist);
                    // echo "<br>";
                    FsSeedsFulldata::insert($inlist);
                }
            }

            //刪除資料表內的資料
            FsSeedsRecord1::truncate();
            
            // return redirect('/fushan/seeds/entry');
        }

        
        // echo $test;

        return [
            'result' => 'ok',
            'test'=> $traps, 
            // 'data' => $redata,
            'finishnote' => $finishnote
        ];


    }

}