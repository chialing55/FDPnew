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


Route::post("/login2", [App\Http\Controllers\LoginController::class, 'login'])->name('login');

Route::get('/choice', [App\Http\Controllers\ChoiceController::class, 'check'])->name('choice');


Route::prefix('fushan')->group(function () {
    Route::get('{project}', function($project) {
        switch ($project) {
            case 'seedling':
                return App::call('App\Http\Controllers\Fushan\SeedlingController@seedling', ['site' => 'fushan']);
            case 'tree':
                return App::call('App\Http\Controllers\Fushan\TreeController@tree', ['site' => 'fushan']);
            case 'seeds':
                return App::call('App\Http\Controllers\Fushan\SeedsController@seeds', ['site' => 'fushan']);
            default:
                abort(404);
        }
    });
});


Route::get('/shoushan/{project}', function($project){
        switch ($project) {
            case 'plot':
                return App::call('App\Http\Controllers\Shoushan\PlotController@plot',['site' => 'shoushan'] );
        }

});


// pages
Route::get('/fushan/{project}/{type}', function($project, $type){
// echo '1'.$project;
  $site='fushan';
        if ($project=='seedling'){

            switch ($type){
                case 'doc': return App::call('App\Http\Controllers\Fushan\SeedlingController@seedling',['site' => $site] );
                case 'note': return App::call('App\Http\Controllers\Fushan\SeedlingController@note',['site' => $site] );
                case 'entry1': return App::call('App\Http\Controllers\Fushan\SeedlingController@entry',['site' => $site, 'entry'=> '1'] );
                case 'entry2': return App::call('App\Http\Controllers\Fushan\SeedlingController@entry',['site' => $site, 'entry'=> '2'] );
                case 'compare': return App::call('App\Http\Controllers\Fushan\SeedlingController@compare',['site' => $site] );
                case 'import': return App::call('App\Http\Controllers\Fushan\SeedlingController@import',['site' => $site] );
                case 'dataviewer': return App::call('App\Http\Controllers\Fushan\SeedlingController@dataviewer',['site' => $site] );
            }

        }

        else if ($project=='tree'){

            switch ($type){
                case 'doc': return App::call('App\Http\Controllers\Fushan\TreeController@tree',['site' => $site] );
                case 'note': return App::call('App\Http\Controllers\Fushan\TreeController@note',['site' => $site] );
                case 'entry1': return App::call('App\Http\Controllers\Fushan\TreeController@entry',['site' => $site, 'entry'=>'1'] );
                case 'entry2': return App::call('App\Http\Controllers\Fushan\TreeController@entry',['site' => $site, 'entry' => '2'] );
                case 'progress': return App::call('App\Http\Controllers\Fushan\TreeController@progress',['site' => $site] );
                case 'dataviewer': return App::call('App\Http\Controllers\Fushan\TreeController@dataviewer',['site' => $site] );
                case 'entryprogress': return App::call('App\Http\Controllers\Fushan\TreeController@entryprogress',['site' => $site] );
                case 'compare': return App::call('App\Http\Controllers\Fushan\TreeController@compare',['site' => $site] );
                case 'modifyPathway': return App::call('App\Http\Controllers\Fushan\TreeController@modifyPathway',['site' => $site] );
                case 'updateTable': return App::call('App\Http\Controllers\Fushan\TreeController@updateTable',['site' => $site] );
                case 'updateBackData': return App::call('App\Http\Controllers\Fushan\TreeController@updateBackData',['site' => $site] );
                case 'addData': return App::call('App\Http\Controllers\Fushan\TreeController@addData',['site' => $site] );
                case 'map': return App::call('App\Http\Controllers\Fushan\TreeController@map',['site' => $site] );
            }

        }

        else if ($project=='seeds'){
            switch ($type) {
                case 'doc': return App::call('App\Http\Controllers\Fushan\SeedsController@seeds', ['site' => $site]);
                case 'note': return App::call('App\Http\Controllers\Fushan\SeedsController@note', ['site' => $site]);
                case 'entry': return App::call('App\Http\Controllers\Fushan\SeedsController@entry', ['site' => $site]);
                case 'showdata': return App::call('App\Http\Controllers\Fushan\SeedsController@showdata', ['site' => $site]);
                case 'unknown': return App::call('App\Http\Controllers\Fushan\SeedsController@unknown', ['site' => $site]);
                case 'updateBackData': return App::call('App\Http\Controllers\Fushan\SeedsController@updateBackData', ['site' => $site]);
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
                    return App::call('App\Http\Controllers\Shoushan\PlotController@plot', ['site' => $site]);
                case '1ha_note':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@note1ha', ['site' => $site]);
                case '1ha_entry1':
                case '1ha_entry2':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@entry1ha', ['site' => $site, 'entry' => substr($type, -1)]);
                case '1ha_compare':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@compare1ha', ['site' => $site]);
                case '10m_note':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@note10m', ['site' => $site]);
                case '10m_entry1':
                case '10m_entry2':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@entry10m', ['site' => $site, 'entry' => substr($type, -1)]);
                case '10m_compare':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@compare10m', ['site' => $site]);
                case '10m_dataviewer':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@dataviewer10m', ['site' => $site]);
                case '1ha_dataviewer':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@dataviewer1ha', ['site' => $site]);
                case '1ha_update':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@update1ha', ['site' => $site]);
                case '10m_update':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@update10m', ['site' => $site]);
                case '1ha_map':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@map1ha', ['site' => $site]);
                case '10m_map':
                    return App::call('App\Http\Controllers\Shoushan\PlotController@map10m', ['site' => $site]);
                // // Add more cases if needed
                // default:
                //     // Handle the case where $type does not match any of the above
                //     break;
            }

        }

});



Auth::routes();



//fstree

Route::prefix('fstree')->group(function () {
    Route::get('record-pdf/{qx}/{qy}/{type}', [App\Http\Controllers\Fushan\TreePDFController::class, 'record']);
    Route::get('deletedata/{stemid}/{entry}/{thispage}', [App\Http\Controllers\Fushan\TreeSaveController::class, 'deletedata']);
    Route::post('savedata', [App\Http\Controllers\Fushan\TreeSaveController::class, 'savedata']);
    Route::post('saverecruit', [App\Http\Controllers\Fushan\TreeSaveController::class, 'saverecruit']);
    Route::get('addalternote/{stemid}/{entry}/{thispage}', [App\Http\Controllers\Fushan\TreeAlternote::class, 'alternote']);
    Route::post('savealternote', [App\Http\Controllers\Fushan\TreeSaveController::class, 'savealternote']);
    Route::post('update', [App\Http\Controllers\Fushan\TreeSaveController::class, 'saveupdate']);
    Route::get('deletealter/{stemid}/{entry}/{thispage}', [App\Http\Controllers\Fushan\TreeSaveController::class, 'deletealter']);
    Route::get('finish/{qx}/{qy}/{entry}', [App\Http\Controllers\Fushan\TreeSaveController::class, 'finishnote']);
    Route::post('deletecensusdata', [App\Http\Controllers\Fushan\TreeSaveController::class, 'deleteCensusData']);
    Route::post('adddata', [App\Http\Controllers\Fushan\TreeSaveController::class, 'addData']);
});

//fsseedling

Route::prefix('fsseedling')->group(function () {
    Route::get('record-pdf/{start}/{end}', [App\Http\Controllers\Fushan\SeedlingPDFController::class, 'record']);
    Route::get('compare-pdf', [App\Http\Controllers\Fushan\SeedlingPDFController::class, 'compare']);
    Route::post('savecov', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'savecov'])->name('savecov');
    Route::post('savedata', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'savedata'])->name('savedata');
    Route::post('saverecruit', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'saverecruit'])->name('saverecruit');
    Route::post('saveslroll/{entry}/{trap}', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'saveslroll'])->name('saveslroll');
    Route::get('deletedata/{tag}/{entry}/{thispage}', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'deletedata'])->name('deletedata');
    Route::get('deleteslroll/{tag}/{id}/{entry}/{trap}', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'deleteslroll'])->name('deleteslroll');
    Route::get('addalternote/{tag}/{entry}/{thispage}', [App\Http\Controllers\Fushan\SeedlingAlternote::class, 'alternote']);
    Route::post('savealternote', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'savealternote']);
    Route::get('deletealter/{stemid}/{entry}/{thispage}', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'deletealter']);
    Route::get('finish/{entry}', [App\Http\Controllers\Fushan\SeedlingSaveController::class, 'finishnote']);
});

//fsseeds
Route::prefix('fsseeds')->group(function () {
    Route::post('savedata/{type}', [App\Http\Controllers\Fushan\SeedsSaveController::class, 'savedata'])->name('savedata');
    Route::post('savedata1/{type}', [App\Http\Controllers\Fushan\SeedsSaveController::class, 'savedata1'])->name('savedata1');
    Route::get('deletedata/{id}/{info}/{thispage}/{type}', [App\Http\Controllers\Fushan\SeedsSaveController::class, 'deletedata'])->name('deletedata');
    Route::get('finish', [App\Http\Controllers\Fushan\SeedsSaveController::class, 'finishnote'])->name('finishnote');
});




Route::prefix('ssPlot')->group(function () {
    Route::get('10m-record-pdf/{plot}', [App\Http\Controllers\Shoushan\S10mTreePDFController::class, 'record']);
    Route::get('1ha-record-pdf/{qx}/{qy}', [App\Http\Controllers\Shoushan\S1haPDFController::class, 'record']);
    Route::post('saveenvi', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'saveenvi']);
    Route::post('savedata', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'savedata']);
    Route::post('saverecruit', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'saverecruit']);
    Route::get('deletedata/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'deletedata']);
    Route::get('alternote/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\Shoushan\PlotAlternote::class, 'alternote']);
    Route::post('savealternote', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'savealternote']);
    Route::get('deletealter/{stemid}/{entry}/{plotType}/{thispage}', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'deletealter']);
    Route::post('update', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'saveupdate']);
    Route::post('deletecensusdata', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'deleteCensusData']);

    Route::post('10msaveaddcov', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'saveaddcov']);
    Route::get('10mdeletecov/{id}/{entry}', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'deletecov']);
    Route::post('10msavecov', [App\Http\Controllers\Shoushan\PlotSaveController::class, 'savecov']);
});


//ssplot entry




//檔案最新更新日期
Route::get('/latest-updates', 'App\Http\Controllers\UpdateController@latestUpdates');





//web


Route::prefix('web')->group(function () {
    Route::get('/splist', [App\Http\Controllers\Web\WebIndexController::class, 'splist']);
    Route::get('/species/{spcode}', [App\Http\Controllers\Web\WebIndexController::class, 'species']);
});
