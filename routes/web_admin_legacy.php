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


use App\Http\Controllers\LoginController;
use App\Http\Controllers\ChoiceController;
use App\Http\Controllers\Fushan\SeedlingController;
use App\Http\Controllers\Fushan\TreeController;
use App\Http\Controllers\Fushan\SeedsController;
use App\Http\Controllers\Shoushan\PlotController;

// Fushan
use App\Http\Controllers\Fushan\TreePDFController;
use App\Http\Controllers\Fushan\TreeSaveController;
use App\Http\Controllers\Fushan\TreeAlternote;
use App\Http\Controllers\Fushan\SeedlingPDFController;
use App\Http\Controllers\Fushan\SeedlingSaveController;
use App\Http\Controllers\Fushan\SeedlingAlternote;
use App\Http\Controllers\Fushan\SeedsSaveController;

// Shoushan
use App\Http\Controllers\Shoushan\S10mTreePDFController;
use App\Http\Controllers\Shoushan\S1haPDFController;
use App\Http\Controllers\Shoushan\PlotSaveController;
use App\Http\Controllers\Shoushan\PlotAlternote;



// === 後台登入與入口 ===

Route::get('/login1', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->group(function () {

    // 後台登入頁：/admin/login
    Route::get('/login', function () {
        return view('login1');
    })->name('admin.login');

    // 登入送出：/admin/login2
    Route::post('/login2', [LoginController::class, 'login'])->name('admin.login.post');

    // 選擇工作頁：/admin/choice
    Route::get('/choice', [ChoiceController::class, 'check'])->name('admin.choice');

});


// 原本：Route::prefix('fushan')->group(function () { Route::get('{project}', function($project) { ... }); });
Route::prefix('admin/fushan')->group(function () {

    // /fushan/seedling  -> SeedlingController@seedling
    Route::get('/seedling', [SeedlingController::class, 'seedling'])->defaults('site', 'fushan');

    // /fushan/tree      -> TreeController@tree
    Route::get('/tree', [TreeController::class, 'tree'])->defaults('site', 'fushan');

    // /fushan/seeds     -> SeedsController@seeds
    Route::get('/seeds', [SeedsController::class, 'seeds'])->defaults('site', 'fushan');

   // ===== seedling pages (原本 /fushan/seedling/{type}) =====
    Route::get('/seedling/doc', [SeedlingController::class, 'seedling'])->defaults('site', 'fushan');
    Route::get('/seedling/note', [SeedlingController::class, 'note'])->defaults('site', 'fushan');
    Route::get('/seedling/entry1', [SeedlingController::class, 'entry'])->defaults('site', 'fushan')->defaults('entry', '1');
    Route::get('/seedling/entry2', [SeedlingController::class, 'entry'])->defaults('site', 'fushan')->defaults('entry', '2');
    Route::get('/seedling/compare', [SeedlingController::class, 'compare'])->defaults('site', 'fushan');
    Route::get('/seedling/import', [SeedlingController::class, 'import'])->defaults('site', 'fushan');
    Route::get('/seedling/dataviewer', [SeedlingController::class, 'dataviewer'])->defaults('site', 'fushan');

    // ===== tree pages (原本 /fushan/tree/{type}) =====
    Route::get('/tree/doc', [TreeController::class, 'tree'])->defaults('site', 'fushan');
    Route::get('/tree/note', [TreeController::class, 'note'])->defaults('site', 'fushan');
    Route::get('/tree/entry1', [TreeController::class, 'entry'])->defaults('site', 'fushan')->defaults('entry', '1');
    Route::get('/tree/entry2', [TreeController::class, 'entry'])->defaults('site', 'fushan')->defaults('entry', '2');
    Route::get('/tree/progress', [TreeController::class, 'progress'])->defaults('site', 'fushan');
    Route::get('/tree/dataviewer', [TreeController::class, 'dataviewer'])->defaults('site', 'fushan');
    Route::get('/tree/entryprogress', [TreeController::class, 'entryprogress'])->defaults('site', 'fushan');
    Route::get('/tree/compare', [TreeController::class, 'compare'])->defaults('site', 'fushan');
    Route::get('/tree/modifyPathway', [TreeController::class, 'modifyPathway'])->defaults('site', 'fushan');
    Route::get('/tree/updateTable', [TreeController::class, 'updateTable'])->defaults('site', 'fushan');
    Route::get('/tree/updateBackData', [TreeController::class, 'updateBackData'])->defaults('site', 'fushan');
    Route::get('/tree/addData', [TreeController::class, 'addData'])->defaults('site', 'fushan');
    Route::get('/tree/map', [TreeController::class, 'map'])->defaults('site', 'fushan');

    // ===== seeds pages (原本 /fushan/seeds/{type}) =====

    Route::get('/seeds/doc', [SeedsController::class, 'seeds'])->defaults('site', 'fushan');
    Route::get('/seeds/note', [SeedsController::class, 'note'])->defaults('site', 'fushan');
    Route::get('/seeds/entry', [SeedsController::class, 'entry'])->defaults('site', 'fushan');
    Route::get('/seeds/showdata', [SeedsController::class, 'showdata'])->defaults('site', 'fushan');
    Route::get('/seeds/unknown', [SeedsController::class, 'unknown'])->defaults('site', 'fushan');
    Route::get('/seeds/updateBackData', [SeedsController::class, 'updateBackData'])->defaults('site', 'fushan');
});


// =====================
// Shoushan routes
// =====================

// 原本：Route::get('/shoushan/{project}', function($project) {...});
// 以及 /shoushan/{project}/{type}
Route::prefix('admin/shoushan')->group(function () {

    Route::get('/plot', [PlotController::class, 'plot'])->defaults('site', 'shoushan');

    // ===== plot pages (原本 /shoushan/plot/{type}) =====

    Route::get('/plot/doc', [PlotController::class, 'plot'])->defaults('site', 'shoushan');
    Route::get('/plot/1ha_note', [PlotController::class, 'note1ha'])->defaults('site', 'shoushan');
    Route::get('/plot/1ha_entry1', [PlotController::class, 'entry1ha'])->defaults('site', 'shoushan')->defaults('entry', '1');
    Route::get('/plot/1ha_entry2', [PlotController::class, 'entry1ha'])->defaults('site', 'shoushan')->defaults('entry', '2');
    Route::get('/plot/1ha_compare', [PlotController::class, 'compare1ha'])->defaults('site', 'shoushan');
    Route::get('/plot/10m_note', [PlotController::class, 'note10m'])->defaults('site', 'shoushan');
    Route::get('/plot/10m_entry1', [PlotController::class, 'entry10m'])->defaults('site', 'shoushan')->defaults('entry', '1');
    Route::get('/plot/10m_entry2', [PlotController::class, 'entry10m'])->defaults('site', 'shoushan')->defaults('entry', '2');
    Route::get('/plot/10m_compare', [PlotController::class, 'compare10m'])->defaults('site', 'shoushan');
    Route::get('/plot/10m_dataviewer', [PlotController::class, 'dataviewer10m'])->defaults('site', 'shoushan');
    Route::get('/plot/1ha_dataviewer', [PlotController::class, 'dataviewer1ha'])->defaults('site', 'shoushan');
    Route::get('/plot/1ha_update', [PlotController::class, 'update1ha'])->defaults('site', 'shoushan');
    Route::get('/plot/10m_update', [PlotController::class, 'update10m'])->defaults('site', 'shoushan');
    Route::get('/plot/1ha_map', [PlotController::class, 'map1ha'])->defaults('site', 'shoushan');
    Route::get('/plot/10m_map', [PlotController::class, 'map10m'])->defaults('site', 'shoushan');
});

// =====================
// fstree
// =====================

Route::prefix('fstree')->group(function () {
    Route::get('record-pdf/{qx}/{qy}/{type}', [TreePDFController::class, 'record']);

    Route::get('deletedata/{stemid}/{entry}/{thispage}', [TreeSaveController::class, 'deletedata']);
    Route::post('savedata', [TreeSaveController::class, 'savedata']);
    Route::post('saverecruit', [TreeSaveController::class, 'saverecruit']);

    Route::get('addalternote/{stemid}/{entry}/{thispage}', [TreeAlternote::class, 'alternote']);
    Route::post('savealternote', [TreeSaveController::class, 'savealternote']);

    Route::post('update', [TreeSaveController::class, 'saveupdate']);
    Route::get('deletealter/{stemid}/{entry}/{thispage}', [TreeSaveController::class, 'deletealter']);

    Route::get('finish/{qx}/{qy}/{entry}', [TreeSaveController::class, 'finishnote']);

    Route::post('deletecensusdata', [TreeSaveController::class, 'deleteCensusData']);
    Route::post('adddata', [TreeSaveController::class, 'addData']);
});

// =====================
// fsseedling
// =====================

Route::prefix('fsseedling')->group(function () {
    Route::get('record-pdf/{start}/{end}', [SeedlingPDFController::class, 'record']);
    Route::get('compare-pdf', [SeedlingPDFController::class, 'compare']);

    Route::post('savecov', [SeedlingSaveController::class, 'savecov'])->name('savecov');
    Route::post('savedata', [SeedlingSaveController::class, 'savedata'])->name('savedata');
    Route::post('saverecruit', [SeedlingSaveController::class, 'saverecruit'])->name('saverecruit');

    Route::post('saveslroll/{entry}/{trap}', [SeedlingSaveController::class, 'saveslroll'])->name('saveslroll');

    Route::get('deletedata/{tag}/{entry}/{thispage}', [SeedlingSaveController::class, 'deletedata'])->name('deletedata');
    Route::get('deleteslroll/{tag}/{id}/{entry}/{trap}', [SeedlingSaveController::class, 'deleteslroll'])->name('deleteslroll');

    Route::get('addalternote/{tag}/{entry}/{thispage}', [SeedlingAlternote::class, 'alternote']);
    Route::post('savealternote', [SeedlingSaveController::class, 'savealternote']);

    Route::get('deletealter/{stemid}/{entry}/{thispage}', [SeedlingSaveController::class, 'deletealter']);
    Route::get('finish/{entry}', [SeedlingSaveController::class, 'finishnote']);
});

// =====================
// fsseeds
// =====================

Route::prefix('fsseeds')->group(function () {
    Route::post('savedata/{type}', [SeedsSaveController::class, 'savedata'])->name('savedata');
    Route::post('savedata1/{type}', [SeedsSaveController::class, 'savedata1'])->name('savedata1');

    Route::get('deletedata/{id}/{info}/{thispage}/{type}', [SeedsSaveController::class, 'deletedata'])->name('deletedata');

    Route::get('finish', [SeedsSaveController::class, 'finishnote'])->name('finishnote');
});

// =====================
// ssPlot
// =====================

Route::prefix('ssPlot')->group(function () {
    Route::get('10m-record-pdf/{plot}', [S10mTreePDFController::class, 'record']);
    Route::get('1ha-record-pdf/{qx}/{qy}', [S1haPDFController::class, 'record']);

    Route::post('saveenvi', [PlotSaveController::class, 'saveenvi']);
    Route::post('savedata', [PlotSaveController::class, 'savedata']);
    Route::post('saverecruit', [PlotSaveController::class, 'saverecruit']);

    Route::get('deletedata/{stemid}/{entry}/{plotType}/{thispage}', [PlotSaveController::class, 'deletedata']);
    Route::get('alternote/{stemid}/{entry}/{plotType}/{thispage}', [PlotAlternote::class, 'alternote']);
    Route::post('savealternote', [PlotSaveController::class, 'savealternote']);
    Route::get('deletealter/{stemid}/{entry}/{plotType}/{thispage}', [PlotSaveController::class, 'deletealter']);

    Route::post('update', [PlotSaveController::class, 'saveupdate']);
    Route::post('deletecensusdata', [PlotSaveController::class, 'deleteCensusData']);

    Route::post('10msaveaddcov', [PlotSaveController::class, 'saveaddcov']);
    Route::get('10mdeletecov/{id}/{entry}', [PlotSaveController::class, 'deletecov']);
    Route::post('10msavecov', [PlotSaveController::class, 'savecov']);
});


//ssplot entry




//檔案最新更新日期
Route::get('/latest-updates', 'App\Http\Controllers\UpdateController@latestUpdates');

