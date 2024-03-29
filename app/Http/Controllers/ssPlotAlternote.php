<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;



use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;
use App\Models\Ss1haRecord1;
use App\Models\Ss1haRecord2;



class SsPlotAlternote extends Controller
{

    public function alternote(Request $request, $stemid, $entry, $plotType, $thispage){


        if ($plotType=='ss10m'){
            if ($entry == '1') {
                $table= new Ss10mTreeRecord1;
            } else {
                $table= new Ss10mTreeRecord2;
            }
            $alterdata=['stemid'=>$stemid, 'plot'=>'', 'sqx'=>'', 'sqy' => '', 'tag'=>'', 'b'=>'', 'csp'=>'', 'other'=>'', '原POM' =>''];
        } else {

            if ($entry == '1') {
                $table= new Ss1haRecord1;
            } else {
                $table= new Ss1haRecord2;
            }
            $alterdata=['stemid'=>$stemid, 'qx'=>'','qy'=>'',  'sqx'=>'', 'sqy' => '', 'tag'=>'', 'b'=>'', 'csp'=>'', 'other'=>'', '原POM' =>''];
        }

        $user = $request->session()->get('user', function () {
            return 'no';
        });


        $result=$table::where('stemid', 'like', $stemid)->get()->toArray();
        

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
