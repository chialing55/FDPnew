<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;


use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;

class ssPlotController extends Controller
{

    public function plot(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });



        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {
            // echo "1";
            //最近一次調

            // print_r($user);
            return view('pages/shoushan/ssplot_doc', [
                'site' => $site,
                'project' => '樣區監測',
                'user' => $user,

            ]);
        }
    }


    public function note(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {
            // echo "1";
            //最近一次調

            // print_r($user);
            return view('pages/shoushan/sstree_note', [
                'site' => $site,
                'project' => '每木',
                'user' => $user

            ]);
        }
    }






}
