<?php

namespace App\Http\Controllers\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Models\Ss10mQuad2014;
use App\Models\Ss10mTree2014;
use App\Models\Ss10mTree2015;
use App\Models\Ss10mTreeRecord1;
use App\Models\Ss10mTreeRecord2;
use App\Models\Ss10mTreeEnviR1;
use App\Models\Ss10mTreeEnviR2;

use App\Models\Ss1haData2015;
use App\Models\Ss1haRecord1;
use App\Models\Ss1haRecord2;
use App\Models\Ss1haEnviR1;
use App\Models\Ss1haEnviR2;

//分配網址至各個頁面

class PlotController extends Controller
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
                'project' => '樣區監測',
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
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }
//產生資料表
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
        // 產生輸入表單
            if (Schema::connection('mysql5')->hasTable('1ha_record1'))
            {
                  //有輸入表單
            } else {
                DB::connection('mysql5')->select('CREATE TABLE 1ha_record1 LIKE 1ha_data_2015;');
                DB::connection('mysql5')->statement("ALTER TABLE 1ha_record1 ENGINE = MyISAM;");
                DB::connection('mysql5')->statement("INSERT IGNORE INTO 1ha_record1 SELECT * FROM 1ha_data_2015");

                DB::connection('mysql5')->statement("ALTER TABLE  `1ha_record1` ADD COLUMN (`date` char(10) not null, `code` char(10) not null,`ill` int(1) default '0',`leave` float default '0',`show` int(1) not null default '1',`confirm` char(2) not null, `tofix` char(2) not null, `alternote` varchar(255) CHARACTER SET utf8 NOT NULL)");
                //status=0, status=-3&branch=0

                Ss1haRecord1::where('branch','!=', '0')->where('status', 'like', '-3')->update(['show'=>'0']);
                Ss1haRecord1::where('status', 'like', '0')->update(['show'=>'0']);
            //分支status=-1, -2 show=0
                Ss1haRecord1::where('status', 'like', '-1')->where('branch', '!=', '0')->update(['show'=>'0']);
                Ss1haRecord1::where('status', 'like', '-2')->where('branch', '!=', '0')->update(['show'=>'0']);

                Ss1haRecord1::query()->update(['dbh'=>'0', 'height'=>'0', 'date'=>'0000-00-00']);
                Ss1haRecord1::where('status', 'like', '-9')->update(['status'=>'']);

                //刪除欄位
                DB::connection('mysql5')->statement("ALTER TABLE `1ha_record1` DROP COLUMN `height`");

                //產生record2
                DB::connection('mysql5')->select('CREATE TABLE 1ha_record2 LIKE 1ha_record1');
           
                DB::connection('mysql5')->select("INSERT INTO 1ha_record2 SELECT * FROM 1ha_record1");

            }


            if (Schema::connection('mysql5')->hasTable('1ha_envi_r1'))
            {
                  //有輸入表單
            } else {
                DB::connection('mysql5')->select('CREATE TABLE 1ha_envi_r1 (`id`  INT(11) NOT NULL AUTO_INCREMENT, `qx` int(2) not null, `qy` int(2) not null, `rocky` float not null, `exposed_surface` float not null, `litter_cover` float not null, `fallen_tree` float not null, `arenga` float not null, `T1` float not null, `T2` float not null, `S` float not null, `H` float not null, `updated_id` char(20) not null, `updated_at` char(100) not null , PRIMARY KEY (  `id` ) , index(  `qx`, `qy`  )) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8');


                for ($x=-4; $x<11;$x++){
                    for ($y=13;$y<20;$y++){
                        $insert=[];
                        $insert=['qx'=> $x, 'qy'=> $y, 'rocky'=>'0', 'rocky'=>'0','exposed_surface'=>'0','litter_cover'=>'0','fallen_tree'=>'0','arenga'=>'0','T1'=>'0', 'H'=>'0','T2'=>'0','S'=>'0', 'updated_id'=>'', 'updated_at'=>''];
                        Ss1haEnviR1::insert($insert);
                    }
                }

                //產生1ha_envi_r2
                DB::connection('mysql5')->select('CREATE TABLE 1ha_envi_r2 LIKE 1ha_envi_r1');
                DB::connection('mysql5')->select("INSERT INTO 1ha_envi_r2 SELECT * FROM 1ha_envi_r1");
     
           
                // DB::connection('mysql5')->select("INSERT INTO 1ha_envi_r2 SELECT * FROM 1ha_envi_r1");

            }

            // print_r($user);
            return view('pages/shoushan/1ha_entry', [
                'site' => $site,
                'project' => '樣區監測',
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


            //檢查是否有輸入資料表

            if (Schema::connection('mysql5')->hasTable('10m_tree_record1'))
            {
                  //有輸入表單
            } else {

                DB::connection('mysql5')->select('CREATE TABLE 10m_tree_record1 LIKE 10m_tree_2015');
                DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_record1 SELECT * FROM 10m_tree_2015");


                DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_record1 SELECT * FROM 10m_tree_2014 WHERE stemid NOT IN (SELECT stemid FROM 10m_tree_2015)");


                DB::connection('mysql5')->statement("ALTER TABLE  `10m_tree_record1` ADD  (`code` char(10) not null,`ill` int(1) default '0',`leave` float default '0',`show` int(1) not null default '1',`confirm` char(2) not null, `tofix` char(2) not null, `alternote` varchar(255) not null, `updated_id` CHAR(20) NOT NULL,`updated_at` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL)");
                //status=0, status=-3&branch=0

                Ss10mTreeRecord1::where('branch','!=', '0')->where('status', 'like', '-3')->update(['show'=>'0']);
                Ss10mTreeRecord1::where('status', 'like', '0')->update(['show'=>'0']);
            //分支status=-1, -2 show=0
                Ss10mTreeRecord1::where('status', 'like', '-1')->where('branch', '!=', '0')->update(['show'=>'0']);
                Ss10mTreeRecord1::where('status', 'like', '-2')->where('branch', '!=', '0')->update(['show'=>'0']);

                Ss10mTreeRecord1::query()->update(['dbh'=>'0', 'height'=>'0', 'date'=>'0000-00-00']);
                Ss10mTreeRecord1::where('status', 'like', '-9')->update(['status'=>'']);

                //刪除欄位
                DB::connection('mysql5')->statement("ALTER TABLE `10m_tree_record1` DROP COLUMN `height`");

                //產生record2
                DB::connection('mysql5')->select('CREATE TABLE 10m_tree_record2 LIKE 10m_tree_record1');
           
                DB::connection('mysql5')->select("INSERT INTO 10m_tree_record2 SELECT * FROM 10m_tree_record1");

            }

            if (Schema::connection('mysql5')->hasTable('10m_tree_envi_r1'))
            {
                  //有輸入表單
            } else {
                DB::connection('mysql5')->select('CREATE TABLE 10m_tree_envi_r1 LIKE 10m_quad_2014');
                DB::connection('mysql5')->statement("INSERT IGNORE INTO 10m_tree_envi_r1 SELECT * FROM 10m_quad_2014 where type like '森林'");

                DB::connection('mysql5')->statement("ALTER TABLE  `10m_tree_envi_r1` ADD  (`sqx` int(2) not null,`sqy` int(2) not null, `note` varchar(255) not Null,  `date` CHAR(10) NOT NULL default '0000-00-00'), `updated_id` CHAR(20) NOT NULL,`updated_at` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
                //刪除欄位
                DB::connection('mysql5')->statement("ALTER TABLE `10m_tree_envi_r1` DROP COLUMN `plot_2015`, DROP COLUMN `plot_2012`, DROP COLUMN `type`, DROP COLUMN `gps_x`, DROP COLUMN `gps_y`, `altitude`, DROP COLUMN `cluster`");
                //改欄位名稱
                DB::connection('mysql5')->statement("ALTER TABLE `10m_tree_envi_r1` CHANGE COLUMN `plot_2023` `plot`");

                //篩選森林資料
                $plotlist=Ss10mTreeEnviR1::query()->get()->toArray();
            }

            // print_r($user);
            return view('pages/shoushan/10m_entry', [
                'site' => $site,
                'project' => '樣區監測',
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
                'project' => '樣區監測',
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
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }


    public function dataviewer10m(Request $request, $site){

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
            return view('pages/shoushan/10m_dataviewer', [
                'site' => $site,
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }    

    public function dataviewer1ha(Request $request, $site){

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
            return view('pages/shoushan/1ha_dataviewer', [
                'site' => $site,
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }

    public function update10m(Request $request, $site){

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
            return view('pages/shoushan/10m_update', [
                'site' => $site,
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }

    public function update1ha(Request $request, $site){

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
            return view('pages/shoushan/1ha_update', [
                'site' => $site,
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }

    public function map10m(Request $request, $site){

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
            return view('pages/shoushan/10m_map', [
                'site' => $site,
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }

    public function map1ha(Request $request, $site){

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
            return view('pages/shoushan/1ha_map', [
                'site' => $site,
                'project' => '樣區監測',
                'user' => $user

            ]);
        }
    }


}

