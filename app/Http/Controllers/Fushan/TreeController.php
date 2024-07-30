<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Models\FsBaseSpinfo;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;


//分配網址到各個頁面
class TreeController extends Controller
{

    public function tree(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

//物種清單，以SPCODE為KEY，傳入session

        $splist=[];

        $splists=FsBaseSpinfo::select('spcode', 'csp')->where('tree', 'like', '1')->get()->toArray();
        foreach($splists as $splist1){
            $splist[$splist1['spcode']]=$splist1['csp'];
        }
        // print_r($splist);
        $request->session()->put('splist', $splist);

        

//產生record1，record2 
        if (Schema::connection('mysql')->hasTable('record1')){
        //     //有輸入表單
        } else {
            DB::connection('mysql')->select('CREATE TABLE record1 LIKE census4');
       
            DB::connection('mysql')->statement("INSERT IGNORE INTO record1 SELECT * FROM census4 where census4.deleted_at like ''");

            DB::connection('mysql')->statement("ALTER TABLE `record1` CHANGE `date` `date` CHAR(10) NOT NULL");
            DB::connection('mysql')->statement("ALTER TABLE `record1` CHANGE `alternote` `alternote` VARCHAR(255) NOT NULL");

            DB::connection('mysql')->statement("ALTER TABLE  `record1` ADD  ( `spcode` char(6) not null, `csp` char(50) not null, `qx` char(2) not null, `qy` char(2), `sqx` int(1) not null, `sqy` int(1), `show` char(1) not null default '1',index(qx),index(qy), index(sqx), index(sqy), index(spcode))");


            DB::connection('mysql')->statement("ALTER TABLE `record1` CHANGE `update_date` `updated_at` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
            //join base表單，更新樣區及種類
            DB::connection('mysql')->table('record1')->join('base', 'record1.tag', '=', 'base.tag')->update(['record1.qx'=>DB::raw('base.qx'), 'record1.qy'=>DB::raw('base.qy'),'record1.sqx'=>DB::raw('base.sqx'),'record1.sqy'=>DB::raw('base.sqy'),'record1.spcode'=>DB::raw('base.spcode')]);
            //選擇輸入樣區時再把csp填入
        //刪除欄位
            DB::connection('mysql')->statement("ALTER TABLE `record1` DROP COLUMN `deleted_at`");
            //status=0, status=-3&branch=0   =>show=0
            FsTreeRecord1::where('branch','!=', '0')->where('status', 'like', '-3')->update(['show'=>'0']);
            FsTreeRecord1::where('status', 'like', '0')->update(['show'=>'0']);

            //分支status=-1, -2 => show=0
            FsTreeRecord1::where('status', 'like', '-1')->where('branch', '!=', '0')->update(['show'=>'0']);
            FsTreeRecord1::where('status', 'like', '-2')->where('branch', '!=', '0')->update(['show'=>'0']);

            //把census4已沒有資料的(date='0000-00-00')的show=0
            FsTreeRecord1::where('date', 'like', '0000-00-00')->update(['show'=>'0']);

            //選擇輸入樣區時再把census3=-1的show改為0
            //前兩次調查已為 -1 的植株，show=0
            
            FsTreeRecord1::query()->update(['dbh'=>'0', 'h2'=>'0', 'date'=>'0000-00-00', 'code'=>'', 'updated_id' =>'', 'tocheck' =>'', 'tofix' =>'', 'confirm' =>'', 'alternote' => '']);
            FsTreeRecord1::where('status', 'like', '-9')->update(['status'=>'']);

            //產生record2
            DB::connection('mysql')->select('CREATE TABLE record2 LIKE record1');
       
            DB::connection('mysql')->select("INSERT INTO record2 SELECT * FROM record1");

         }


        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {
            // echo "1";
            //最近一次調

            // print_r($user);
            return view('pages/fushan/tree_doc', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
                'census' => 5


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
            return view('pages/fushan/tree_note', [
                'site' => $site,
                'project' => '每木',
                'user' => $user

            ]);
        }
    }


    public function entry(Request $request, $site, $entry){

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
            return view('pages/fushan/tree_entry', [
                'site' => $site,
                'project' => '每木',
                'entry' => $entry,
                'user' => $user,
            ]);
        }
    }

    public function progress(Request $request, $site){

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
            return view('pages/fushan/tree_progress', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function dataviewer(Request $request, $site){

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
            return view('pages/fushan/tree_dataviewer', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function entryprogress(Request $request, $site){

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
            return view('pages/fushan/tree_entryprogress', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function compare(Request $request, $site){

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
            return view('pages/fushan/tree_compare', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function modifyPathway(Request $request, $site){

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
            return view('pages/fushan/tree_modifyPathway', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function updateTable(Request $request, $site){

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
            return view('pages/fushan/tree_updateTable', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function updateBackData(Request $request, $site){

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
            return view('pages/fushan/tree_updateBackData', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function addData(Request $request, $site){

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
            return view('pages/fushan/tree_addData', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

    public function map(Request $request, $site){

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
            return view('pages/fushan/tree_map', [
                'site' => $site,
                'project' => '每木',
                'user' => $user,
            ]);
        }
    }

}
