<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    
    return view('login1');
});

Route::get('/login1', function () {
    
    return view('login1');
});

// Route::get("/login2", function(){
//     return ('hello');
// })

Route::post("/login2", [App\Http\Controllers\loginController::class, 'login'])->name('login');



Route::get('/choice', [App\Http\Controllers\choiceController::class, 'check'])->name('choice');

// Route::get('seedling/{site}/{project}/{user}', [App\Http\Controllers\seedlingController::class, 'seedling'])->name('seedling');;

// Route::get("seedling", function(){
//     $site=$_GET['site'];
//     $project=$_GET['projcet'];
//     // return('hello');
//     return redirect(route('genus', ['family' => $family, 'genusnow' => $genus]));
// });

Route::get('/{site}/{project}', function($site, $project){
// echo '1'.$project;
    if ($site=='fushan'){
        if ($project=='seedling'){
            return App::call('App\Http\Controllers\fsSeedlingController@seedling',['site' => $site] );
        }

        if ($project=='tree'){
            return App::call('App\Http\Controllers\fsTreeController@tree',['site' => $site] );
        }
    }
});

//seedling download record pdf
//
Route::get('/fsseedling-record-pdf/{start}/{end}', [App\Http\Controllers\fsSeedlingPDFController::class, 'record']);

Auth::routes();

//tree download record pdf

Route::get('/fstree-record-pdf/{qx}/{qy}/{type}', [App\Http\Controllers\fsTreePDFController::class, 'record']);

// pages
Route::get('/fushan/{project}/{type}', function($project, $type){
// echo '1'.$project;
  $site='fushan';
        if ($project=='seedling'){

            if ($type=='doc'){
                return App::call('App\Http\Controllers\fsSeedlingController@seedling',['site' => $site] );
            } else if ($type=='note'){
                return App::call('App\Http\Controllers\fsSeedlingController@note',['site' => $site] );
            } else if ($type=='entry1'){
                return App::call('App\Http\Controllers\fsSeedlingController@entry',['site' => $site, 'entry'=> '1'] );
            } else if ($type=='entry2'){
                return App::call('App\Http\Controllers\fsSeedlingController@entry',['site' => $site, 'entry'=> '2'] );
            } else if ($type=='compare'){
                return App::call('App\Http\Controllers\fsSeedlingController@compare',['site' => $site] );
            } else if ($type=='import'){
                return App::call('App\Http\Controllers\fsSeedlingController@import',['site' => $site] );
            }

        }

        else if ($project=='tree'){
            if ($type=='doc'){
                return App::call('App\Http\Controllers\fsTreeController@tree',['site' => $site] );
            } else if ($type=='note'){
                return App::call('App\Http\Controllers\fsTreeController@note',['site' => $site] );
            } else if ($type=='entry1'){
                return App::call('App\Http\Controllers\fsTreeController@entry',['site' => $site, 'entry'=>'1'] );
            } else if ($type=='entry2'){
                return App::call('App\Http\Controllers\fsTreeController@entry',['site' => $site, 'entry' => '2'] );
            } else if ($type=='progress'){
                return App::call('App\Http\Controllers\fsTreeController@progress',['site' => $site] );
            } else if ($type=='dataviewer'){
                return App::call('App\Http\Controllers\fsTreeController@dataviewer',['site' => $site] );
            } else if ($type=='entryprogress'){
                return App::call('App\Http\Controllers\fsTreeController@entryprogress',['site' => $site] );
            }
            
        }
    
});

// Route::get('/fushan/seedling/text', [App\Http\Controllers\fsSeedlingController::class, 'seedling',['site' => 'fushan']]);

//fstree entry
Route::get('/fstreedeletedata/{stemid}/{entry}/{thispage}', [App\Http\Controllers\fsTreeSaveController::class, 'deletedata']);
Route::post('/fstreesavedata', [App\Http\Controllers\fsTreeSaveController::class, 'savedata']);
Route::post('/fstreesaverecruit', [App\Http\Controllers\fsTreeSaveController::class, 'saverecruit']);
Route::get('/fstreeaddalternote/{stemid}/{entry}/{thispage}', [App\Http\Controllers\fsTreeAlternote::class, 'alternote']);
Route::post('/fstreesavealternote', [App\Http\Controllers\fsTreeSaveController::class, 'savealternote']);
Route::get('/fstreedeletealter/{stemid}/{entry}/{thispage}', [App\Http\Controllers\fsTreeSaveController::class, 'deletealter']);
Route::get('/fstreefinish/{qx}/{qy}/{entry}', [App\Http\Controllers\fsTreeSaveController::class, 'finishnote']);

//fsseedling entry
Route::post('/fsseedlingsavecov', [App\Http\Controllers\fsSeedlingSaveController::class, 'savecov'])->name('savecov');
Route::post('/fsseedlingsavedata', [App\Http\Controllers\fsSeedlingSaveController::class, 'savedata'])->name('savedata');
Route::post('/fsseedlingsaverecruit', [App\Http\Controllers\fsSeedlingSaveController::class, 'saverecruit'])->name('saverecruit');
Route::post('/fsseedlingsaveslroll/{entry}/{trap}', [App\Http\Controllers\fsSeedlingSaveController::class, 'saveslroll'])->name('saveslroll');
Route::get('/fsseedlingdeletedata/{tag}/{entry}/{thispage}', [App\Http\Controllers\fsSeedlingSaveController::class, 'deletedata'])->name('deletedata');
Route::get('/fsseedlingdeleteslroll/{tag}/{id}/{entry}/{trap}', [App\Http\Controllers\fsSeedlingSaveController::class, 'deleteslroll'])->name('deleteslroll');
Route::get('/fsseedlingaddalternote/{tag}/{entry}/{thispage}', [App\Http\Controllers\fsSeedlingAlternote::class, 'alternote']);

Route::post('/fsseedlingsavealternote', [App\Http\Controllers\fsSeedlingSaveController::class, 'savealternote']);
Route::get('/fsseedlingdeletealter/{stemid}/{entry}/{thispage}', [App\Http\Controllers\fsSeedlingSaveController::class, 'deletealter']);



//檔案最新更新日期
Route::get('/latest-updates', 'App\Http\Controllers\UpdateController@latestUpdates');
