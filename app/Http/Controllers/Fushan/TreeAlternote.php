<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;


use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;

//每木特殊修改
//將各輸入欄位轉成json格式存入資料庫中
class TreeAlternote extends Controller
{

    public function alternote(Request $request, $stemid, $entry, $thispage){


        if ($entry=='1'){
            $table= new FsTreeRecord1;
        } else {
            $table= new FsTreeRecord2;
        }

        $user = $request->session()->get('user', function () {
            return 'no';
        });




        $result=$table::where('stemid', 'like', $stemid)->get()->toArray();
        $alterdata=['stemid'=>$stemid, 'qx'=>'', 'qy' => '', 'sqx'=>'', 'sqy' => '', 'tag'=>'', 'b'=>'', 'csp'=>'', 'dbh(<1)'=>'',  'pom'=>'', 'other'=>''];

        if ($result[0]['alternote']==''){
            $mergedArray=$alterdata;
            $havedata='no';
        } else {
            //$string = "a:1, b:2";
            //把json轉array
            $alterdata1 = json_decode($result[0]['alternote'], true);
            $mergedArray = array_merge($alterdata, $alterdata1);
            $havedata='yes';
        }



        return [
            'result' => 'ok',
            'stemid' => $stemid,
            'entry' => $entry,
            'user' => $user,
            'thispage' => $thispage,
            'alterdata' => $mergedArray,
            // 'csplist' => $csplist,
            'havedata' => $havedata

        ];        

        
    }







}
