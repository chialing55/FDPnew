<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;



use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;


class ss10mAlternote extends Controller
{

    public function alternote(Request $request, $stemid, $entry){


        if ($entry=='1'){
            $table= new Ss10mTreeRecord1;
        } else {
            $table= new Ss10mTreeRecord2;
        }

        $user = $request->session()->get('user', function () {
            return 'no';
        });




        $result=$table::where('stemid', 'like', $stemid)->get()->toArray();
        $alterdata=['stemid'=>$stemid, 'plot'=>'', 'sqx'=>'', 'sqy' => '', 'tag'=>'', 'b'=>'', 'csp'=>''];

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
            'alterdata' => $mergedArray,
            // 'csplist' => $csplist,
            'havedata' => $havedata

        ];        

        
    }







}
