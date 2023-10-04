<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingBase;
use App\Models\FsSeedlingCov;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;


class fsSeedlingController extends Controller
{


    public function seedling(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {
            //最近一次調查
            $maxCensus=FsSeedlingData::max('census');
           

            //檢查是否有紀錄紙資料表

            if (Schema::connection('mysql3')->hasTable('slrecord'))
            {
               
                  //有紀錄紙
     
            } else {
                //沒有紀錄紙，建一個新表
              // echo 'n';
                $c_table=DB::connection('mysql3')->select("create table slrecord as select seedling.*, base.x,base.y from seedling left join base on seedling.mtag = base.mtag where seedling.census like ? and (seedling.status like 'A' or seedling.status like 'N') order by seedling.trap, seedling.plot, seedling.tag", [$maxCensus]);
                //刪除死亡的萌蘗苗(個體未死)  高度 = -7, sprout = True
                $d_record=DB::connection('mysql3')->select("delete from slrecord where ht = '-7' and sprout ='True'");
                //刪除-2的萌蘗苗(個體DBH>1)  高度 = -2, sprout = True
                $d_record=DB::connection('mysql3')->select("delete from slrecord where ht = '-2' and sprout ='True'");


                Schema::connection('mysql3')->table('slrecord', function($table)
                {
                    $table->string('updated_at');
                });

                //將欄位變空白
                $u_record=FsSeedlingSlrecord::query()->update(['id' => '0', 'census' => $maxCensus+1, 'year' => '0','month' => '0', 'date' => '0000-00-00']);
                //將 recruit =R 變成 O, status = N 的 recruit 變成 N 
                $u_record=FsSeedlingSlrecord::where('recruit', 'R')->update(['recruit'=>'O']);
                $u_record=FsSeedlingSlrecord::where('status', 'N')->update(['recruit'=>'N']);




                //建立輸入表單1&2


                $c_table1=DB::connection('mysql3')->select("CREATE  TABLE  `fs_seedling`.`slrecord1` (  `id` int( 11  )  NOT  NULL AUTO_INCREMENT,  `census` int( 3  )  NOT  NULL ,  `year` int( 4  )  NOT  NULL ,  `month` int( 2  )  NOT  NULL ,  `date` char( 10  )  NOT  NULL ,  `trap` int( 3  )  NOT  NULL ,  `plot` int( 1  )  NOT  NULL ,  `tag` char( 12  )  NOT  NULL ,  `mtag` char( 12  )  NOT  NULL ,  `csp` char( 20  )  NOT  NULL ,    `ht` float ,  `cotno` int( 2  )  , `leafno` int( 2  )   ,  `ind` int( 3  )  NOT  NULL default  '1',  `note` varchar( 255  )  NOT  NULL ,`recruit` char( 2  )  NOT  NULL ,`status` char( 2  )  NOT  NULL ,`sprout` char( 5  )  NOT  NULL , `x` int( 3  )  NOT  NULL , `y` int( 3  )  NOT  NULL , `updated_at` varchar(255) ,  PRIMARY KEY (  `id` ) , index(  trap  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8");
                $i_table1=DB::connection('mysql3')->select("INSERT INTO fs_seedling.slrecord1 SELECT * FROM fs_seedling.slrecord");
                $a_table1=DB::connection('mysql3')->select("ALTER TABLE  `slrecord1` ADD  (`alternote` VARCHAR( 255 ) NOT NULL, `update_id` char(20) not null)");

                $census=FsSeedlingSlrecord1::first();

            if ($census['census']%2==0){
                $month='8';
            } else { $month = '2';}
         

                $u_record=FsSeedlingSlrecord1::query()->update(['year' => date('Y'), 'month' => $month]);
                $u_record=FsSeedlingSlrecord1::where('ht','>=','-1')->update(['ht' => NULL, 'cotno' => NULL, 'leafno' => NULL]);
                $u_record=FsSeedlingSlrecord1::query()->update(['updated_at' => '']);

                //輸入表單2
              $c_table2=DB::connection('mysql3')->select("CREATE TABLE slrecord2 LIKE slrecord1");

                $i_table1=DB::connection('mysql3')->select("INSERT INTO fs_seedling.slrecord2 SELECT * FROM fs_seedling.slrecord1");

                //cov
                $c_cov1=DB::connection('mysql3')->select("create table `fs_seedling`.`slcov1` ( `id` int (11) NOT  NULL AUTO_INCREMENT, `year` int( 4  ) ,  `month` int( 2  ) ,  `date` char( 10  ) ,  `trap` int( 3  ),  `plot` int( 1  ) , `cov` float,`ht` float, `canopy` char (2) ,  `note` varchar( 255  ), `updated_at` varchar(255), `update_id` char(20), PRIMARY KEY (  `id` ) , index(  trap  ) )ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8");

                for ($x=1;$x<108;$x++){ 
                    if ($x!=42){    
                        for ($y=1;$y<4;$y++){
                        $i_table1=DB::connection('mysql3')->select("INSERT INTO fs_seedling.slcov1 (trap, plot) values ('$x', '$y')");    
                   
                }}}

                $d_table1=DB::connection('mysql3')->select("delete from fs_seedling.slcov1 where trap = 33 and plot = 3"); 

                $u_cov=FsSeedlingSlcov1::query()->update(['year' => date('Y'), 'month' => $month, 'date' => '0000-00-00']); 

                $u_cov=FsSeedlingSlcov1::query()->update(['updated_at' => '']);  

                $c_cov2=DB::connection('mysql3')->select("CREATE TABLE slcov2 LIKE slcov1");

                $i_table2=DB::connection('mysql3')->select("INSERT INTO fs_seedling.slcov2 SELECT * FROM fs_seedling.slcov1");

                $c_roll=DB::connection('mysql3')->select("create table `fs_seedling`.`slroll1` ( `id` int (11) NOT  NULL AUTO_INCREMENT,  `year` int( 4  )  NOT  NULL ,  `month` int( 2  )  NOT  NULL , `date` char( 10  )  NOT  NULL ,  `trap` int( 3  )  NOT  NULL ,  `plot` int( 1  )  NOT  NULL , `tag` char(12) not null,  `note` varchar( 255  ), `updated_at` varchar(255) not null, `update_id` char(20) not null,PRIMARY KEY (  `id` ) , index(  trap  ) )ENGINE  =  MyISAM  DEFAULT CHARSET  = utf8");
                $c_roll=DB::connection('mysql3')->select("CREATE TABLE slroll2 LIKE slroll1");


            }

            // $slrecord=FsSeedlingSlrecord::all();
            // for($i=0; $i<count($slrecord);$i++){
            //     $plot=$slrecord[$i]['trap'];
            //     $slrecorddata[$plot][]=$slrecord[$i];
            // }
            // echo count($slrecord)/500;
            // print_r($slrecord[0])

            $slrecord=FsSeedlingSlrecord::first();
            // $year=$slrecord[0]['year'];
            $census=$slrecord['census'];
            // print_r($census);


            // $slcov1=FsSeedlingSlcov1::all();
            // $slcov2=FsSeedlingSlcov2::all();
            // $slroll1=FsSeedlingSlroll1::all();
            // $slroll2=FsSeedlingSlroll2::all();



            
            return view('pages/fushan/seedling_doc', [
                'site' => $site,
                'project' => '小苗',
                'user' => $user,
                // 'maxcensus' => $maxCensus,
                'census' => $census,
                // 'entry1note' => $entry1note,
                // 'entry2note' => $entry2note
                // 'slrecord' => $slrecord,
                // 'slrecord1' => $slrecord1,
                // 'slrecord2' => $slrecord2
                // 'slcov1' => $slcov1,
                // 'slcov2' => $slcov2,
                // 'slroll1' => $slroll1,
                // 'slroll2' => $slroll2
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
            $slrecord=FsSeedlingSlrecord::first();
            // $year=$slrecord[0]['year'];
            $census=$slrecord['census'];

            return view('pages/fushan/seedling_entry', [
                'site' => $site,
                'project' => '小苗',
                'user' => $user,
                'entry' => $entry,
                'census' => $census,

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
            $slrecord=FsSeedlingSlrecord::first();
            // $year=$slrecord[0]['year'];
            $census=$slrecord['census'];

            return view('pages/fushan/seedling_compare', [
                'site' => $site,
                'project' => '小苗',
                'user' => $user,
                'census' => $census,

            ]);
        }
    }

    public function import(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {
            $slrecord=FsSeedlingSlrecord::first();
            // $year=$slrecord[0]['year'];
            $census=$slrecord['census'];

            return view('pages/fushan/seedling_import', [
                'site' => $site,
                'project' => '小苗',
                'user' => $user,
                'census' => $census,

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


            return view('pages/fushan/seedling_note', [
                'site' => $site,
                'project' => '小苗',
                'user' => $user,
                

            ]);
        }
    }

}
