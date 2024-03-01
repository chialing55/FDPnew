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

Route::get('/fushan/{project}', function($project){
// echo '1'.$project;

        if ($project=='seedling'){
            return App::call('App\Http\Controllers\fsSeedlingController@seedling',['site' => 'fushan'] );
        }

        if ($project=='tree'){
            return App::call('App\Http\Controllers\fsTreeController@tree',['site' => 'fushan'] );
        }

        if ($project=='seeds'){
            return App::call('App\Http\Controllers\fsSeedsController@seeds',['site' => 'fushan'] );
        }
    
});

Route::get('/shoushan/{project}', function($project){
// echo '1'.$project;
        if ($project=='plot'){
            return App::call('App\Http\Controllers\ssPlotController@plot',['site' => 'shoushan'] );
        }
    
});

//seedling download record pdf
//
Route::get('/fsseedling-record-pdf/{start}/{end}', [App\Http\Controllers\fsSeedlingPDFController::class, 'record']);
Route::get('/fsseedling-compare-pdf', [App\Http\Controllers\fsSeedlingPDFController::class, 'compare']);

Auth::routes();

//tree download record pdf

Route::get('/fstree-record-pdf/{qx}/{qy}/{type}', [App\Http\Controllers\fsTreePDFController::class, 'record']);

//ssplot download record pdf

Route::get('/ssplot-10m-record-pdf/{plot}', [App\Http\Controllers\ss10mTreePDFController::class, 'record']);
Route::get('/ssplot-1ha-record-pdf/{qx}/{qy}', [App\Http\Controllers\ss1haPDFController::class, 'record']);
// pages
Route::get('/fushan/{project}/{type}', function($project, $type){
// echo '1'.$project;
  $site='fushan';
        if ($project=='seedling'){

            switch ($type){
                case 'doc': return App::call('App\Http\Controllers\fsSeedlingController@seedling',['site' => $site] );
                case 'note': return App::call('App\Http\Controllers\fsSeedlingController@note',['site' => $site] );
                case 'entry1': return App::call('App\Http\Controllers\fsSeedlingController@entry',['site' => $site, 'entry'=> '1'] );
                case 'entry2': return App::call('App\Http\Controllers\fsSeedlingController@entry',['site' => $site, 'entry'=> '2'] );
                case 'compare': return App::call('App\Http\Controllers\fsSeedlingController@compare',['site' => $site] );
                case 'import': return App::call('App\Http\Controllers\fsSeedlingController@import',['site' => $site] );
                case 'dataviewer': return App::call('App\Http\Controllers\fsSeedlingController@dataviewer',['site' => $site] );
            }

        }

        else if ($project=='tree'){

            switch ($type){
                case 'doc': return App::call('App\Http\Controllers\fsTreeController@tree',['site' => $site] );
                case 'note': return App::call('App\Http\Controllers\fsTreeController@note',['site' => $site] );
                case 'entry1': return App::call('App\Http\Controllers\fsTreeController@entry',['site' => $site, 'entry'=>'1'] );
                case 'entry2': return App::call('App\Http\Controllers\fsTreeController@entry',['site' => $site, 'entry' => '2'] );
                case 'progress': return App::call('App\Http\Controllers\fsTreeController@progress',['site' => $site] );
                case 'dataviewer': return App::call('App\Http\Controllers\fsTreeController@dataviewer',['site' => $site] );
                case 'entryprogress': return App::call('App\Http\Controllers\fsTreeController@entryprogress',['site' => $site] );
                case 'compare': return App::call('App\Http\Controllers\fsTreeController@compare',['site' => $site] );
                case 'modifyPathway': return App::call('App\Http\Controllers\fsTreeController@modifyPathway',['site' => $site] );
                case 'updateTable': return App::call('App\Http\Controllers\fsTreeController@updateTable',['site' => $site] );
                case 'updateBackData': return App::call('App\Http\Controllers\fsTreeController@updateBackData',['site' => $site] );
                case 'addData': return App::call('App\Http\Controllers\fsTreeController@addData',['site' => $site] );
            }

        }

        else if ($project=='seeds'){
            switch ($type) {
                case 'doc': return App::call('App\Http\Controllers\fsSeedsController@seeds', ['site' => $site]);
                case 'note': return App::call('App\Http\Controllers\fsSeedsController@note', ['site' => $site]);
                case 'entry': return App::call('App\Http\Controllers\fsSeedsController@entry', ['site' => $site]);
                case 'showdata': return App::call('App\Http\Controllers\fsSeedsController@showdata', ['site' => $site]);
                case 'unknown': return App::call('App\Http\Controllers\fsSeedsController@unknown', ['site' => $site]);
                case 'updateBackData': return App::call('App\Http\Controllers\fsSeedsController@updateBackData', ['site' => $site]);
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
                    return App::call('App\Http\Controllers\ssPlotController@plot', ['site' => $site]);
                case '1ha_note':
                    return App::call('App\Http\Controllers\ssPlotController@note1ha', ['site' => $site]);
                case '1ha_entry1':
                case '1ha_entry2':
                    return App::call('App\Http\Controllers\ssPlotController@entry1ha', ['site' => $site, 'entry' => substr($type, -1)]);
                case '1ha_compare':
                    return App::call('App\Http\Controllers\ssPlotController@compare1ha', ['site' => $site]);
                case '10m_note':
                    return App::call('App\Http\Controllers\ssPlotController@note10m', ['site' => $site]);
                case '10m_entry1':
                case '10m_entry2':
                    return App::call('App\Http\Controllers\ssPlotController@entry10m', ['site' => $site, 'entry' => substr($type, -1)]);
                case '10m_compare':
                    return App::call('App\Http\Controllers\ssPlotController@compare10m', ['site' => $site]);
                case '10m_dataviewer':
                    return App::call('App\Http\Controllers\ssPlotController@dataviewer10m', ['site' => $site]);
                case '1ha_dataviewer':
                    return App::call('App\Http\Controllers\ssPlotController@dataviewer1ha', ['site' => $site]);

                // // Add more cases if needed
                // default:
                //     // Handle the case where $type does not match any of the above
                //     break;
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
Route::post('/fstreeupdate', [App\Http\Controllers\fsTreeSaveController::class, 'saveupdate']);
Route::get('/fstreedeletealter/{stemid}/{entry}/{thispage}', [App\Http\Controllers\fsTreeSaveController::class, 'deletealter']);
Route::get('/fstreefinish/{qx}/{qy}/{entry}', [App\Http\Controllers\fsTreeSaveController::class, 'finishnote']);
Route::post('/fstreedeletecensusdata', [App\Http\Controllers\fsTreeSaveController::class, 'fsTreeDeleteCensusData']);
Route::post('/fstreeadddata', [App\Http\Controllers\fsTreeSaveController::class, 'fsTreeAddData']);

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
Route::get('/fsseedlingfinish/{entry}', [App\Http\Controllers\fsSeedlingSaveController::class, 'finishnote']);


Route::post('/fsseedssavedata/{type}', [App\Http\Controllers\fsSeedsSaveController::class, 'savedata'])->name('savedata');
Route::post('/fsseedssavedata1/{type}', [App\Http\Controllers\fsSeedsSaveController::class, 'savedata1'])->name('savedata1');
Route::get('/fsseedsdeletedata/{id}/{info}/{thispage}/{type}', [App\Http\Controllers\fsSeedsSaveController::class, 'deletedata'])->name('deletedata');
Route::get('/fsseedsfinish', [App\Http\Controllers\fsSeedsSaveController::class, 'finishnote'])->name('finishnote');





//ssplot entry
Route::post('/ssPlotsaveenvi', [App\Http\Controllers\ssPlotSaveController::class, 'saveenvi']);
Route::post('/ssPlotsavedata', [App\Http\Controllers\ssPlotSaveController::class, 'savedata']);
Route::post('/ssPlotsaverecruit', [App\Http\Controllers\ssPlotSaveController::class, 'saverecruit']);
// Route::post('/ss1hasaverecruit', [App\Http\Controllers\ssPlotSaveController::class, 'saverecruit1ha']);
Route::get('/ssPlotdeletedata/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\ssPlotSaveController::class, 'deletedata']);
Route::get('/ssPlotalternote/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\ssPlotAlternote::class, 'alternote']);
Route::post('/ssPlotsavealternote', [App\Http\Controllers\ssPlotSaveController::class, 'savealternote']);
Route::get('/ssPlotdeletealter/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\ssPlotSaveController::class, 'deletealter']);
Route::post('/ss10msaveaddcov', [App\Http\Controllers\ssPlotSaveController::class, 'saveaddcov']);
Route::get('/ss10mdeletecov/{id}/{entry}', [App\Http\Controllers\ssPlotSaveController::class, 'deletecov']);
Route::post('/ss10msavecov', [App\Http\Controllers\ssPlotSaveController::class, 'savecov']);



//檔案最新更新日期
Route::get('/latest-updates', 'App\Http\Controllers\UpdateController@latestUpdates');





//web

Route::get("web/index", [App\Http\Controllers\webIndexController::class, 'index']);

Route::get('web/splist', function () {
    
    return view('pages/web/splist');
});

Route::get('web/species/{spcode}', function ($spcode) {
    
    return view('pages/web/species',['spcode'=>$spcode]);
});

// Route::get("web/splist", [App\Http\Controllers\webIndexController::class, 'splist']);
