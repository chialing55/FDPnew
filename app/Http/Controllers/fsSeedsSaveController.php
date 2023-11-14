<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;

use App\Jobs\FsSeedsCheck;
use App\Jobs\fsSeedsAddButton;


class fsSeedsSaveController extends Controller
{


    public function spinfo(){
        $spinfolist=FsSeedsSplist::query()->get()->toArray();
        foreach ($spinfolist as $spinfo1){
            $spinfo[$spinfo1['csp']]=$spinfo1;
        }

        return $spinfo;
    }

//修改表單
    public function savedata(Request $request){

        $data_all = request()->all();
        // // print_r($savecov);
        $data=$data_all['data'];
        // $entry=$data_all['entry'];
        $user = $request->session()->get('user', function () {
            return view('login1', [
            'check' => 'no'
            ]);
        });
        // $user=$data[0]['user'];
        // // $temp=[];
        // $list='';
        $datasavenote='';

        $spinfo=$this->spinfo();

        // $spinfolist=FsSeedsSplist::query()->get()->toArray();
        // foreach ($spinfolist as $spinfo1){
        //     $spinfo[$spinfo1['csp']]=$spinfo1;
        // }


        for ($i=0;$i<count($data);$i++){
            // $list[]=$data[$i]['tag'];
                $data[$i]['checknote']='';
                $data[$i]['trap'] = str_pad($data[$i]['trap'], 3, '0', STR_PAD_LEFT);
                $check = new fsSeedsCheck;
                $checknote=$check->check($data[$i], $spinfo, 'o');
                $result=$check->check($data[$i], $spinfo, 'o');
                $checknote=$result['checknote'];
                $data[$i]=$result['result'];
                // // $checknote1.=$checknote;
                $uplist=[];

                foreach ($data[$i] as $key => $value){
                    if ($value==Null){$value='';}
                    if ($key=='d'){continue;}

                    $uplist[$key] = $value;
                }

                // if ($checknote==''){$inlist['pass']='y';} else {$inlist['pass']='n';}
                $uplist['checknote']=$checknote;
                $uplist['update_id']=$user;

                // $uplist['updated_at']=date("Y-m-d H:i:s");

                FsSeedsRecord1::where('id', 'like', $data[$i]['id'])->update($uplist);

                $datasavenote='已更新資料';
            // $uplist=[];

       
        } 
//更新data
        $data1=FsSeedsRecord1::query()->orderBy('trap', 'asc')->orderBy('csp', 'asc')->orderBy('code', 'asc')->get()->toArray();
        $ob_table = new fsSeedsAddButton;
        $redata=$ob_table->addbutton($data1);


            return [
                'result' => 'ok',
                'uplist' => $uplist,
                'data' => $redata,
                // 'list' => $list,
                'seedssavenote' => $datasavenote

            ];
        
    }
//空白表單
    public function savedata1(Request $request){

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
            // $list[]=$data[$i]['tag'];
            if ($data[$i]['trap']!=''){
                if ($data[$i]['code']==''){
                    $data[$i]['code']='0';
                }
                // if ($record['seeds']==''){
                //     $record['seeds']='0';
                // }
                $data[$i]['trap'] = str_pad($data[$i]['trap'], 3, '0', STR_PAD_LEFT);
                $check = new fsSeedsCheck;
                // $checknote=$check->check($data[$i], $spinfo, 'n');
                $result=$check->check($data[$i], $spinfo, 'n');
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
                $inlist['update_id']=$user;
                $inlist['updated_at']=date("Y-m-d H:i:s");

                FsSeedsRecord1::insert($inlist);
            }

        } 

//更新data
        $data1=FsSeedsRecord1::query()->orderBy('trap', 'asc')->orderBy('csp', 'asc')->orderBy('code', 'asc')->get()->toArray();
        $ob_table = new fsSeedsAddButton;
        $redata=$ob_table->addbutton($data1);

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
            $emptytable[$k]['identifier']='';
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


    public function deletedata(Request $request, $id, $info, $thispage){
        $test='';
            $user = $request->session()->get('user', function () {
                return view('login1', [
                'check' => 'no'
                ]);
            });
        // $user='chialing';
        $datasavenote='';
    

            $d_record = FsSeedsRecord1::where('id', 'like', $id)->delete();

            $datasavenote='已刪除 '.$info.' 種子雨資料';

            // 重新載入資料

//更新data
        $data1=FsSeedsRecord1::query()->orderBy('trap', 'asc')->orderBy('csp', 'asc')->orderBy('code', 'asc')->get()->toArray();
        $ob_table = new fsSeedsAddButton;
        $redata=$ob_table->addbutton($data1);

        return [
            'result' => 'ok',
            // 'test'=> $test, 
            'data' => $redata,
            'seedssavenote' => $datasavenote
        ];
    }



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
            unset($inlistf['checknote']);
            // print_r($inlistf);
            // $traps=FsSeedsRecord1::select('trap')->groupBy('trap')->get()->toArray();

            for($j=1;$j<108;$j++){
                $trap = str_pad($j, 3, '0', STR_PAD_LEFT);

                $datas=FsSeedsRecord1::query()->where('trap', 'like',$trap)->get()->toArray();
                if (count($datas)>0){

                    for($i=0;$i<count($datas);$i++){

                        unset($datas[$i]['checknote']);
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

                    }

                } else {
                    $inlist=$inlistf;
                    $inlist['trap']=$trap;
                    $inlist['csp']='nothing';
                    $inlist['sp']='NOTHING';
                    $inlist['identified']='Y';
                    $inlist['code']='0';
                    $inlist['count']='0';

                }
                    $inlist['update_id']=$user;
                    $inlist['updated_at']=date("Y-m-d H:i:s");
                    // print_r($inlist);
                    // echo "<br>";
                    FsSeedsFulldata::insert($inlist);

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