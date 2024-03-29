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



Route::get('/choice', [App\Http\Controllers\ChoiceController::class, 'check'])->name('choice');

// Route::get('seedling/{site}/{project}/{user}', [App\Http\Controllers\seedlingController::class, 'seedling'])->name('seedling');;

// Route::get("seedling", function(){
//     $site=$_GET['site'];
//     $project=$_GET['projcet'];
//     // return('hello');
//     return redirect(route('genus', ['family' => $family, 'genusnow' => $genus]));
// });

Route::get('/fushan/{project}', function($project){
// echo '1'.$project;

        if ($project=='seedling'){
            return App::call('App\Http\Controllers\FsSeedlingController@seedling',['site' => 'fushan'] );
        }

        if ($project=='tree'){
            return App::call('App\Http\Controllers\FsTreeController@tree',['site' => 'fushan'] );
        }

        if ($project=='seeds'){
            return App::call('App\Http\Controllers\FsSeedsController@seeds',['site' => 'fushan'] );
        }
    
});

Route::get('/shoushan/{project}', function($project){
// echo '1'.$project;
        if ($project=='plot'){
            return App::call('App\Http\Controllers\SsPlotController@plot',['site' => 'shoushan'] );
        }
    
});

//seedling download record pdf
//
Route::get('/fsseedling-record-pdf/{start}/{end}', [App\Http\Controllers\FsSeedlingPDFController::class, 'record']);
Route::get('/fsseedling-compare-pdf', [App\Http\Controllers\FsSeedlingPDFController::class, 'compare']);

Auth::routes();

//tree download record pdf

Route::get('/fstree-record-pdf/{qx}/{qy}/{type}', [App\Http\Controllers\FsTreePDFController::class, 'record']);

//ssplot download record pdf

Route::get('/ssplot-10m-record-pdf/{plot}', [App\Http\Controllers\Ss10mTreePDFController::class, 'record']);
Route::get('/ssplot-1ha-record-pdf/{qx}/{qy}', [App\Http\Controllers\Ss1haPDFController::class, 'record']);
// pages
Route::get('/fushan/{project}/{type}', function($project, $type){
// echo '1'.$project;
  $site='fushan';
        if ($project=='seedling'){

            switch ($type){
                case 'doc': return App::call('App\Http\Controllers\FsSeedlingController@seedling',['site' => $site] );
                case 'note': return App::call('App\Http\Controllers\FsSeedlingController@note',['site' => $site] );
                case 'entry1': return App::call('App\Http\Controllers\FsSeedlingController@entry',['site' => $site, 'entry'=> '1'] );
                case 'entry2': return App::call('App\Http\Controllers\FsSeedlingController@entry',['site' => $site, 'entry'=> '2'] );
                case 'compare': return App::call('App\Http\Controllers\FsSeedlingController@compare',['site' => $site] );
                case 'import': return App::call('App\Http\Controllers\FsSeedlingController@import',['site' => $site] );
                case 'dataviewer': return App::call('App\Http\Controllers\FsSeedlingController@dataviewer',['site' => $site] );
            }

        }

        else if ($project=='tree'){

            switch ($type){
                case 'doc': return App::call('App\Http\Controllers\FsTreeController@tree',['site' => $site] );
                case 'note': return App::call('App\Http\Controllers\FsTreeController@note',['site' => $site] );
                case 'entry1': return App::call('App\Http\Controllers\FsTreeController@entry',['site' => $site, 'entry'=>'1'] );
                case 'entry2': return App::call('App\Http\Controllers\FsTreeController@entry',['site' => $site, 'entry' => '2'] );
                case 'progress': return App::call('App\Http\Controllers\FsTreeController@progress',['site' => $site] );
                case 'dataviewer': return App::call('App\Http\Controllers\FsTreeController@dataviewer',['site' => $site] );
                case 'entryprogress': return App::call('App\Http\Controllers\FsTreeController@entryprogress',['site' => $site] );
                case 'compare': return App::call('App\Http\Controllers\FsTreeController@compare',['site' => $site] );
                case 'modifyPathway': return App::call('App\Http\Controllers\FsTreeController@modifyPathway',['site' => $site] );
                case 'updateTable': return App::call('App\Http\Controllers\FsTreeController@updateTable',['site' => $site] );
                case 'updateBackData': return App::call('App\Http\Controllers\FsTreeController@updateBackData',['site' => $site] );
                case 'addData': return App::call('App\Http\Controllers\FsTreeController@addData',['site' => $site] );
                case 'map': return App::call('App\Http\Controllers\FsTreeController@map',['site' => $site] );
            }

        }

        else if ($project=='seeds'){
            switch ($type) {
                case 'doc': return App::call('App\Http\Controllers\FsSeedsController@seeds', ['site' => $site]);
                case 'note': return App::call('App\Http\Controllers\FsSeedsController@note', ['site' => $site]);
                case 'entry': return App::call('App\Http\Controllers\FsSeedsController@entry', ['site' => $site]);
                case 'showdata': return App::call('App\Http\Controllers\FsSeedsController@showdata', ['site' => $site]);
                case 'unknown': return App::call('App\Http\Controllers\FsSeedsController@unknown', ['site' => $site]);
                case 'updateBackData': return App::call('App\Http\Controllers\FsSeedsController@updateBackData', ['site' => $site]);
                // Add more cases if needed
                // default:
                //     // Handle the case where $type does not match any of the above
                //     break;
            }
        }
    
});

// pages
Route::get('/shoushan/{project}/{type}', function($project, $type){
// echo '1'.$project;
  $site='shoushan';
        if ($project=='plot'){

            switch ($type) {
                case 'doc':
                    return App::call('App\Http\Controllers\SsPlotController@plot', ['site' => $site]);
                case '1ha_note':
                    return App::call('App\Http\Controllers\SsPlotController@note1ha', ['site' => $site]);
                case '1ha_entry1':
                case '1ha_entry2':
                    return App::call('App\Http\Controllers\SsPlotController@entry1ha', ['site' => $site, 'entry' => substr($type, -1)]);
                case '1ha_compare':
                    return App::call('App\Http\Controllers\SsPlotController@compare1ha', ['site' => $site]);
                case '10m_note':
                    return App::call('App\Http\Controllers\SsPlotController@note10m', ['site' => $site]);
                case '10m_entry1':
                case '10m_entry2':
                    return App::call('App\Http\Controllers\SsPlotController@entry10m', ['site' => $site, 'entry' => substr($type, -1)]);
                case '10m_compare':
                    return App::call('App\Http\Controllers\SsPlotController@compare10m', ['site' => $site]);
                case '10m_dataviewer':
                    return App::call('App\Http\Controllers\SsPlotController@dataviewer10m', ['site' => $site]);
                case '1ha_dataviewer':
                    return App::call('App\Http\Controllers\SsPlotController@dataviewer1ha', ['site' => $site]);

                // // Add more cases if needed
                // default:
                //     // Handle the case where $type does not match any of the above
                //     break;
            }

        }

});



// Route::get('/fushan/seedling/text', [App\Http\Controllers\FsSeedlingController::class, 'seedling',['site' => 'fushan']]);

//fstree entry
Route::get('/fstreedeletedata/{stemid}/{entry}/{thispage}', [App\Http\Controllers\FsTreeSaveController::class, 'deletedata']);
Route::post('/fstreesavedata', [App\Http\Controllers\FsTreeSaveController::class, 'savedata']);
Route::post('/fstreesaverecruit', [App\Http\Controllers\FsTreeSaveController::class, 'saverecruit']);
Route::get('/fstreeaddalternote/{stemid}/{entry}/{thispage}', [App\Http\Controllers\FsTreeAlternote::class, 'alternote']);
Route::post('/fstreesavealternote', [App\Http\Controllers\FsTreeSaveController::class, 'savealternote']);
Route::post('/fstreeupdate', [App\Http\Controllers\FsTreeSaveController::class, 'saveupdate']);
Route::get('/fstreedeletealter/{stemid}/{entry}/{thispage}', [App\Http\Controllers\FsTreeSaveController::class, 'deletealter']);
Route::get('/fstreefinish/{qx}/{qy}/{entry}', [App\Http\Controllers\FsTreeSaveController::class, 'finishnote']);
Route::post('/fstreedeletecensusdata', [App\Http\Controllers\FsTreeSaveController::class, 'deleteCensusData']);
Route::post('/fstreeadddata', [App\Http\Controllers\FsTreeSaveController::class, 'addData']);

//fsseedling entry
Route::post('/fsseedlingsavecov', [App\Http\Controllers\FsSeedlingSaveController::class, 'savecov'])->name('savecov');
Route::post('/fsseedlingsavedata', [App\Http\Controllers\FsSeedlingSaveController::class, 'savedata'])->name('savedata');
Route::post('/fsseedlingsaverecruit', [App\Http\Controllers\FsSeedlingSaveController::class, 'saverecruit'])->name('saverecruit');
Route::post('/fsseedlingsaveslroll/{entry}/{trap}', [App\Http\Controllers\FsSeedlingSaveController::class, 'saveslroll'])->name('saveslroll');
Route::get('/fsseedlingdeletedata/{tag}/{entry}/{thispage}', [App\Http\Controllers\FsSeedlingSaveController::class, 'deletedata'])->name('deletedata');
Route::get('/fsseedlingdeleteslroll/{tag}/{id}/{entry}/{trap}', [App\Http\Controllers\FsSeedlingSaveController::class, 'deleteslroll'])->name('deleteslroll');
Route::get('/fsseedlingaddalternote/{tag}/{entry}/{thispage}', [App\Http\Controllers\FsSeedlingAlternote::class, 'alternote']);
Route::post('/fsseedlingsavealternote', [App\Http\Controllers\FsSeedlingSaveController::class, 'savealternote']);
Route::get('/fsseedlingdeletealter/{stemid}/{entry}/{thispage}', [App\Http\Controllers\FsSeedlingSaveController::class, 'deletealter']);
Route::get('/fsseedlingfinish/{entry}', [App\Http\Controllers\FsSeedlingSaveController::class, 'finishnote']);


Route::post('/fsseedssavedata/{type}', [App\Http\Controllers\FsSeedsSaveController::class, 'savedata'])->name('savedata');
Route::post('/fsseedssavedata1/{type}', [App\Http\Controllers\FsSeedsSaveController::class, 'savedata1'])->name('savedata1');
Route::get('/fsseedsdeletedata/{id}/{info}/{thispage}/{type}', [App\Http\Controllers\FsSeedsSaveController::class, 'deletedata'])->name('deletedata');
Route::get('/fsseedsfinish', [App\Http\Controllers\FsSeedsSaveController::class, 'finishnote'])->name('finishnote');





//ssplot entry
Route::post('/ssPlotsaveenvi', [App\Http\Controllers\SsPlotSaveController::class, 'saveenvi']);
Route::post('/ssPlotsavedata', [App\Http\Controllers\SsPlotSaveController::class, 'savedata']);
Route::post('/ssPlotsaverecruit', [App\Http\Controllers\SsPlotSaveController::class, 'saverecruit']);
// Route::post('/ss1hasaverecruit', [App\Http\Controllers\SsPlotSaveController::class, 'saverecruit1ha']);
Route::get('/ssPlotdeletedata/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\SsPlotSaveController::class, 'deletedata']);
Route::get('/ssPlotalternote/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\SsPlotAlternote::class, 'alternote']);
Route::post('/ssPlotsavealternote', [App\Http\Controllers\SsPlotSaveController::class, 'savealternote']);
Route::get('/ssPlotdeletealter/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\SsPlotSaveController::class, 'deletealter']);
Route::post('/ss10msaveaddcov', [App\Http\Controllers\SsPlotSaveController::class, 'saveaddcov']);
Route::get('/ss10mdeletecov/{id}/{entry}', [App\Http\Controllers\SsPlotSaveController::class, 'deletecov']);
Route::post('/ss10msavecov', [App\Http\Controllers\SsPlotSaveController::class, 'savecov']);



//檔案最新更新日期
Route::get('/latest-updates', 'App\Http\Controllers\UpdateController@latestUpdates');





//web

Route::get("web/index", [App\Http\Controllers\WebIndexController::class, 'index']);

Route::get('web/splist', function () {
    
    return view('pages/web/splist');
});

Route::get('web/species/{spcode}', function ($spcode) {
    
    return view('pages/web/species',['spcode'=>$spcode]);
});

// Route::get("web/splist", [App\Http\Controllers\webIndexController::class, 'splist']);
