<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;

use App\Models\Ss10mQuad2014;
use App\Models\Ss10mTree2014;
use App\Models\Ss10mTree2015;
use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;

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


    public function note1ha(Request $request, $site){

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
            return view('pages/shoushan/1ha_note', [
                'site' => $site,
                'project' => '樣區監測_1.05樣區',
                'user' => $user

            ]);
        }
    }

    public function note10m(Request $request, $site){

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
            return view('pages/shoushan/10m_note', [
                'site' => $site,
                'project' => '樣區監測_森林觀測樣區',
                'user' => $user

            ]);
        }
    }

    public function entry1ha(Request $request, $site, $entry){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {
            // echo "1";



        //產生紀錄紙




            // print_r($user);
            return view('pages/shoushan/1ha_entry', [
                'site' => $site,
                'project' => '樣區監測_1.05樣區',
                'entry' => $entry,
                'user' => $user

            ]);
        }
    }


    public function entry10m(Request $request, $site, $entry){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {
            // echo "1";


        //產生輸入表單


            //檢查是否有紀錄紙資料表

            if (Schema::connection('mysql5')->hasTable('10m_tree_record1'))
            {
                  //有輸入表單
            } else {

                DB::connection('mysql5')->select('CREATE TABLE 10m_tree_record1 LIKE 10m_tree_2015');
                DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_record1 SELECT * FROM 10m_tree_2015");


                DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_record1 SELECT * FROM 10m_tree_2014 WHERE stemid NOT IN (SELECT stemid FROM 10m_tree_2015)");


                DB::connection('mysql5')->statement("ALTER TABLE  `10m_tree_record1` ADD  (`code` char(10) not null,`ill` int(1) default '0',`leave` float default '0',`show` int(1) not null default '1', `date` CHAR(10) NOT NULL default '0000-00-00',`confirm` char(2) not null, `tofix` char(2) not null, `alternote` varchar(255) not null, `update_id` CHAR(20) NOT NULL,`updated_at` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL)");
                //status=0, status=-3&branch=0

                Ss10mTreeRecord1::where('branch','!=', '0')->where('status', 'like', '-3')->update(['show'=>'0']);
                Ss10mTreeRecord1::where('status', 'like', '0')->update(['show'=>'0']);
            //分支status=-1, -2 show=0
                Ss10mTreeRecord1::where('status', 'like', '-1')->where('branch', '!=', '0')->update(['show'=>'0']);
                Ss10mTreeRecord1::where('status', 'like', '-2')->where('branch', '!=', '0')->update(['show'=>'0']);

                Ss10mTreeRecord1::query()->update(['dbh'=>'0', 'height'=>'0']);
                Ss10mTreeRecord1::where('status', 'like', '-9')->update(['status'=>'']);

                //產生record2
                DB::connection('mysql5')->select('CREATE TABLE 10m_tree_record2 LIKE 10m_tree_record1');
           
                DB::connection('mysql5')->select("INSERT INTO 10m_tree_record2 SELECT * FROM 10m_tree_record1");

            }

            // print_r($user);
            return view('pages/shoushan/10m_entry', [
                'site' => $site,
                'project' => '樣區監測_森林觀測樣區',
                'entry' => $entry,
                'user' => $user

            ]);
        }
    }

    public function compare1ha(Request $request, $site){

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
            return view('pages/shoushan/1ha_compare', [
                'site' => $site,
                'project' => '樣區監測_1.05樣區',
                'entry' => $entry,
                'user' => $user

            ]);
        }
    }


    public function compare10m(Request $request, $site){

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
            return view('pages/shoushan/10m_compare', [
                'site' => $site,
                'project' => '樣區監測_森林觀測樣區',
                'entry' => $entry,
                'user' => $user

            ]);
        }
    }

}
