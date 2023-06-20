<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;



use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;


class fsSeedlingAlternote extends Controller
{

    public function alternote(Request $request, $tag, $entry, $thispage){


        if ($entry=='1'){
            $table= new FsSeedlingSlrecord1;
        } else {
            $table= new FsSeedlingSlrecord2;
        }

        $user = $request->session()->get('user', function () {
            return 'no';
        });


        $result=$table::where('tag', 'like', $tag)->get()->toArray();
        // print_r($result);
        $alterdata=['Tag'=>'', 'Trap'=>'', 'Plot' => '', '原長度'=>'', '原葉片數'=>'', '狀態' => '', 'id'=> $result[0]['id']];
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
            'tag' => $tag,
            'entry' => $entry,
            'user' => $user,
            'thispage' => $thispage,
            'alterdata' => $mergedArray,
            'havedata' => $havedata

        ];        

        
    }







}
