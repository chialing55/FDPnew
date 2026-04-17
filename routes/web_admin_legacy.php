<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ChoiceController;

use App\Http\Controllers\Fushan\SeedlingController;
use App\Http\Controllers\Fushan\TreeController;
use App\Http\Controllers\Fushan\SeedsController;
use App\Http\Controllers\Fushan\MortalityController;
use App\Http\Controllers\Nanjenshan\SeedlingController as NanjenshanSeedlingController;

use App\Http\Controllers\Shoushan\PlotController;

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForcePasswordResetController;
// Fushan actions
use App\Http\Controllers\Fushan\TreePDFController;
use App\Http\Controllers\Fushan\TreeSaveController;
use App\Http\Controllers\Fushan\TreeAlternote;

use App\Http\Controllers\Fushan\SeedlingPDFController;
use App\Http\Controllers\Fushan\SeedlingSaveController;
use App\Http\Controllers\Fushan\SeedlingAlternote;

use App\Http\Controllers\Fushan\SeedsSaveController;

// Shoushan actions
use App\Http\Controllers\Shoushan\S10mTreePDFController;
use App\Http\Controllers\Shoushan\S1haPDFController;
use App\Http\Controllers\Shoushan\PlotSaveController;
use App\Http\Controllers\Shoushan\PlotAlternote;

/*
|--------------------------------------------------------------------------
| Public routes (非後台)
|--------------------------------------------------------------------------
*/

// 檔案最新更新日期
Route::get('/latest-updates', 'App\Http\Controllers\UpdateController@latestUpdates')
    ->name('latest-updates');

/*
|--------------------------------------------------------------------------
| Admin - Auth routes (後台登入/登出/入口)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // 後台登入頁
    // 舊：GET /admin/login-old  -> view('login1') name('admin.login')
    Route::get('/login-old', function () {
        return view('login1');
    })->name('login');

    // 登入送出
    // 舊：POST /admin/login2 -> LoginController@login name('admin.login.post')
    Route::post('/login2', [LoginController::class, 'login'])
        ->name('login.post');

    // 你若未來有 logout，可放這
    // Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin - Protected routes (需要登入)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Choice (工作選擇頁)
    |--------------------------------------------------------------------------
    */
    // 舊：GET /admin/choice -> ChoiceController@check name('admin.choice')
    Route::get('/choice', [ChoiceController::class, 'check'])
        ->name('choice');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::post('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | Fushan - Pages (工作頁：只負責顯示)
    |--------------------------------------------------------------------------
    | 統一：/admin/fushan/{module}/{page}
    */

    // 使用者管理
    Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('{user}/edit', 'edit')->name('edit');
        Route::post('{user}', 'update')->name('update');
        Route::delete('{user}', 'destroy')->name('destroy');

        // 審核
        Route::post('{user}/approve', 'approve')->name('approve');
        Route::post('{user}/reject',  'reject')->name('reject');

        // 管理員重設密碼（發臨時密碼）
        Route::post('{user}/reset-password', 'resetPassword')->name('reset-password');
    });
    // 使用者自行更換密碼（登入後）
    Route::get('force-reset-password', [ForcePasswordResetController::class, 'edit'])
        ->name('password.force.edit');

    Route::post('force-reset-password', [ForcePasswordResetController::class, 'update'])
        ->name('password.force.update');

    Route::prefix('fushan')->name('fushan.')->group(function () {

        // ===== Seedling pages =====
        // 舊：GET /admin/fushan/seedling -> SeedlingController@seedling
        Route::prefix('seedling')->middleware('scope:fushan,seedling')->name('seedling.')->group(function () {
            Route::get('/', [SeedlingController::class, 'seedling'])
                ->defaults('site', 'fushan')
                ->name('index');

            // 舊：/admin/fushan/seedling/doc
            Route::get('/doc', [SeedlingController::class, 'seedling'])
                ->defaults('site', 'fushan')
                ->name('doc');

            // 舊：/admin/fushan/seedling/note
            Route::get('/note', [SeedlingController::class, 'note'])
                ->defaults('site', 'fushan')
                ->name('note');

            // 舊：/admin/fushan/seedling/entry1
            Route::get('/entry1', [SeedlingController::class, 'entry'])
                ->defaults('site', 'fushan')->defaults('entry', '1')
                ->name('entry.1');

            // 舊：/admin/fushan/seedling/entry2
            Route::get('/entry2', [SeedlingController::class, 'entry'])
                ->defaults('site', 'fushan')->defaults('entry', '2')
                ->name('entry.2');

            // 舊：/admin/fushan/seedling/compare
            Route::get('/compare', [SeedlingController::class, 'compare'])
                ->defaults('site', 'fushan')
                ->name('compare');

            // 舊：/admin/fushan/seedling/import
            Route::get('/import', [SeedlingController::class, 'import'])
                ->defaults('site', 'fushan')
                ->name('import');

            // 舊：/admin/fushan/seedling/dataviewer
            Route::get('/dataviewer', [SeedlingController::class, 'dataviewer'])
                ->defaults('site', 'fushan')
                ->name('dataviewer');
        });

        Route::prefix('mortality')->middleware('scope:fushan,mortality')->name('mortality.')->group(function () {
            Route::get('/', [MortalityController::class, 'mortality'])
                ->defaults('site', 'fushan')
                ->name('index');

            Route::get('/doc', [MortalityController::class, 'mortality'])
                ->defaults('site', 'fushan')
                ->name('doc');

            Route::get('/note', [MortalityController::class, 'note'])
                ->defaults('site', 'fushan')
                ->name('note');

            Route::get('/entry', [MortalityController::class, 'entry'])
                ->defaults('site', 'fushan')->defaults('entry', '1')
                ->name('entry');

            Route::get('/entry1', [MortalityController::class, 'entry'])
                ->defaults('site', 'fushan')->defaults('entry', '1')
                ->name('entry.1');

            Route::get('/entry2', [MortalityController::class, 'entry'])
                ->defaults('site', 'fushan')->defaults('entry', '2')
                ->name('entry.2');

            Route::post('/entry/generate', [MortalityController::class, 'generateMortalityEntryTables'])
                ->defaults('site', 'fushan')
                ->name('entry.generate');

            Route::get('/census', [MortalityController::class, 'censusPage'])
                ->defaults('site', 'fushan')
                ->name('census');

            Route::post('/census', [MortalityController::class, 'saveCensusPage'])
                ->defaults('site', 'fushan')
                ->name('census.save');

            Route::get('/survey-import', [MortalityController::class, 'surveyImport'])
                ->defaults('site', 'fushan')
                ->name('survey-import');

            Route::post('/survey-import', [MortalityController::class, 'uploadSurveyImport'])
                ->defaults('site', 'fushan')
                ->name('survey-import.upload');

            Route::get('/record', [MortalityController::class, 'record'])
                ->defaults('site', 'fushan')
                ->name('record');

            Route::get('/compare', [MortalityController::class, 'compare'])
                ->defaults('site', 'fushan')
                ->name('compare');

            Route::get('/import', [MortalityController::class, 'import'])
                ->defaults('site', 'fushan')
                ->name('import');

            Route::get('/dataviewer', [MortalityController::class, 'dataviewer'])
                ->defaults('site', 'fushan')
                ->name('dataviewer');

            Route::get('/process', [MortalityController::class, 'process'])
                ->defaults('site', 'fushan')
                ->name('process');

            Route::post('/process/basic', [MortalityController::class, 'runBasicProcess'])
                ->defaults('site', 'fushan')
                ->name('process.basic');

            Route::post('/process/tree-individuals', [MortalityController::class, 'runTreeIndividualsProcess'])
                ->defaults('site', 'fushan')
                ->name('process.tree-individuals');

            Route::post('/process/people', [MortalityController::class, 'runPeopleProcess'])
                ->defaults('site', 'fushan')
                ->name('process.people');

            Route::post('/process/comments', [MortalityController::class, 'runCommentProcess'])
                ->defaults('site', 'fushan')
                ->name('process.comments');

            Route::post('/process/census-records', [MortalityController::class, 'runCensusRecordImport'])
                ->defaults('site', 'fushan')
                ->name('process.census-records');

            Route::get('/process/comments/review', [MortalityController::class, 'commentReview'])
                ->defaults('site', 'fushan')
                ->name('process.comments.review');

            Route::get('/process/comment-other/review', [MortalityController::class, 'commentOtherReview'])
                ->defaults('site', 'fushan')
                ->name('process.comment-other.review');

            Route::post('/process/comments/options', [MortalityController::class, 'storeCommentOption'])
                ->defaults('site', 'fushan')
                ->name('process.comments.options.store');

            Route::post('/process/comments/review', [MortalityController::class, 'saveCommentReviewPage'])
                ->defaults('site', 'fushan')
                ->name('process.comments.review.save');

            Route::post('/process/comment-other/review', [MortalityController::class, 'saveCommentOtherReviewPage'])
                ->defaults('site', 'fushan')
                ->name('process.comment-other.review.save');
        });

        // ===== Tree pages =====
        // 舊：GET /admin/fushan/tree -> TreeController@tree
        Route::prefix('tree')->middleware('scope:fushan,tree')->name('tree.')->group(function () {
            Route::get('/', [TreeController::class, 'tree'])
                ->defaults('site', 'fushan')
                ->name('index');

            // 舊：/admin/fushan/tree/doc
            Route::get('/doc', [TreeController::class, 'tree'])
                ->defaults('site', 'fushan')
                ->name('doc');

            // 舊：/admin/fushan/tree/note
            Route::get('/note', [TreeController::class, 'note'])
                ->defaults('site', 'fushan')
                ->name('note');

            // 舊：/admin/fushan/tree/entry1
            Route::get('/entry1', [TreeController::class, 'entry'])
                ->defaults('site', 'fushan')->defaults('entry', '1')
                ->name('entry.1');

            // 舊：/admin/fushan/tree/entry2
            Route::get('/entry2', [TreeController::class, 'entry'])
                ->defaults('site', 'fushan')->defaults('entry', '2')
                ->name('entry.2');

            // 舊：/admin/fushan/tree/progress
            Route::get('/progress', [TreeController::class, 'progress'])
                ->defaults('site', 'fushan')
                ->name('progress');

            // 舊：/admin/fushan/tree/dataviewer
            Route::get('/dataviewer', [TreeController::class, 'dataviewer'])
                ->defaults('site', 'fushan')
                ->name('dataviewer');

            // 舊：/admin/fushan/tree/entryprogress
            Route::get('/entryprogress', [TreeController::class, 'entryprogress'])
                ->defaults('site', 'fushan')
                ->name('entryprogress');

            // 舊：/admin/fushan/tree/compare
            Route::get('/compare', [TreeController::class, 'compare'])
                ->defaults('site', 'fushan')
                ->name('compare');

            // 舊：/admin/fushan/tree/modifyPathway
            Route::get('/modify-pathway', [TreeController::class, 'modifyPathway'])
                ->defaults('site', 'fushan')
                ->name('modify-pathway');

            // 舊：/admin/fushan/tree/updateTable
            Route::get('/update-table', [TreeController::class, 'updateTable'])
                ->defaults('site', 'fushan')
                ->name('update-table');

            // 舊：/admin/fushan/tree/updateBackData
            Route::get('/update-back-data', [TreeController::class, 'updateBackData'])
                ->defaults('site', 'fushan')
                ->name('update-back-data');

            // 舊：/admin/fushan/tree/addData
            Route::get('/add-data', [TreeController::class, 'addData'])
                ->defaults('site', 'fushan')
                ->name('add-data');

            // 舊：/admin/fushan/tree/map
            Route::get('/map', [TreeController::class, 'map'])
                ->defaults('site', 'fushan')
                ->name('map');
        });

        // ===== Seeds pages =====
        // 舊：GET /admin/fushan/seeds -> SeedsController@seeds
        Route::prefix('seeds')->middleware('scope:fushan,seeds')->name('seeds.')->group(function () {
            Route::get('/', [SeedsController::class, 'seeds'])
                ->defaults('site', 'fushan')
                ->name('index');

            // 舊：/admin/fushan/seeds/doc
            Route::get('/doc', [SeedsController::class, 'seeds'])
                ->defaults('site', 'fushan')
                ->name('doc');

            // 舊：/admin/fushan/seeds/note
            Route::get('/note', [SeedsController::class, 'note'])
                ->defaults('site', 'fushan')
                ->name('note');

            // 舊：/admin/fushan/seeds/entry
            Route::get('/entry', [SeedsController::class, 'entry'])
                ->defaults('site', 'fushan')
                ->name('entry');

            // 舊：/admin/fushan/seeds/showdata
            Route::get('/showdata', [SeedsController::class, 'showdata'])
                ->defaults('site', 'fushan')
                ->name('showdata');

            // 舊：/admin/fushan/seeds/unknown
            Route::get('/unknown', [SeedsController::class, 'unknown'])
                ->defaults('site', 'fushan')
                ->name('unknown');

            // 舊：/admin/fushan/seeds/updateBackData
            Route::get('/update-back-data', [SeedsController::class, 'updateBackData'])
                ->defaults('site', 'fushan')
                ->name('update-back-data');
        });
    });

    Route::prefix('nanjenshan')->name('nanjenshan.')->group(function () {
        Route::prefix('seedling')->middleware('scope:nanjenshan,seedling')->name('seedling.')->group(function () {
            Route::get('/', [NanjenshanSeedlingController::class, 'doc'])
                ->defaults('site', 'nanjenshan')
                ->name('index');

            Route::get('/doc', [NanjenshanSeedlingController::class, 'doc'])
                ->defaults('site', 'nanjenshan')
                ->name('doc');

            Route::get('/dataviewer', [NanjenshanSeedlingController::class, 'dataviewer'])
                ->defaults('site', 'nanjenshan')
                ->name('dataviewer');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Shoushan - Pages (工作頁：只負責顯示)
    |--------------------------------------------------------------------------
    */

    Route::prefix('shoushan')->name('shoushan.')->group(function () {

        // 舊：GET /admin/shoushan/plot -> PlotController@plot
        Route::prefix('tree')->middleware('scope:shoushan,tree')->name('tree.')->group(function () {

            Route::get('/', [PlotController::class, 'plot'])
                ->defaults('site', 'shoushan')
                ->name('index');

            // 舊：/admin/shoushan/plot/doc
            Route::get('/doc', [PlotController::class, 'plot'])
                ->defaults('site', 'shoushan')
                ->name('doc');

            // 舊：/admin/shoushan/plot/1ha_note
            Route::get('/1ha/note', [PlotController::class, 'note1ha'])
                ->defaults('site', 'shoushan')
                ->name('1ha.note');

            // 舊：/admin/shoushan/plot/1ha_entry1
            Route::get('/1ha/entry1', [PlotController::class, 'entry1ha'])
                ->defaults('site', 'shoushan')->defaults('entry', '1')
                ->name('1ha.entry.1');

            // 舊：/admin/shoushan/plot/1ha_entry2
            Route::get('/1ha/entry2', [PlotController::class, 'entry1ha'])
                ->defaults('site', 'shoushan')->defaults('entry', '2')
                ->name('1ha.entry.2');

            // 舊：/admin/shoushan/plot/1ha_compare
            Route::get('/1ha/compare', [PlotController::class, 'compare1ha'])
                ->defaults('site', 'shoushan')
                ->name('1ha.compare');

            // 舊：/admin/shoushan/plot/1ha_dataviewer
            Route::get('/1ha/dataviewer', [PlotController::class, 'dataviewer1ha'])
                ->defaults('site', 'shoushan')
                ->name('1ha.dataviewer');

            // 舊：/admin/shoushan/plot/1ha_update
            Route::get('/1ha/update', [PlotController::class, 'update1ha'])
                ->defaults('site', 'shoushan')
                ->name('1ha.update');

            // 舊：/admin/shoushan/plot/1ha_map
            Route::get('/1ha/map', [PlotController::class, 'map1ha'])
                ->defaults('site', 'shoushan')
                ->name('1ha.map');


            // 舊：/admin/shoushan/plot/10m_note
            Route::get('/10m/note', [PlotController::class, 'note10m'])
                ->defaults('site', 'shoushan')
                ->name('10m.note');

            // 舊：/admin/shoushan/plot/10m_entry1
            Route::get('/10m/entry1', [PlotController::class, 'entry10m'])
                ->defaults('site', 'shoushan')->defaults('entry', '1')
                ->name('10m.entry.1');

            // 舊：/admin/shoushan/plot/10m_entry2
            Route::get('/10m/entry2', [PlotController::class, 'entry10m'])
                ->defaults('site', 'shoushan')->defaults('entry', '2')
                ->name('10m.entry.2');

            // 舊：/admin/shoushan/plot/10m_compare
            Route::get('/10m/compare', [PlotController::class, 'compare10m'])
                ->defaults('site', 'shoushan')
                ->name('10m.compare');

            // 舊：/admin/shoushan/plot/10m_dataviewer
            Route::get('/10m/dataviewer', [PlotController::class, 'dataviewer10m'])
                ->defaults('site', 'shoushan')
                ->name('10m.dataviewer');

            // 舊：/admin/shoushan/plot/10m_update
            Route::get('/10m/update', [PlotController::class, 'update10m'])
                ->defaults('site', 'shoushan')
                ->name('10m.update');

            // 舊：/admin/shoushan/plot/10m_map
            Route::get('/10m/map', [PlotController::class, 'map10m'])
                ->defaults('site', 'shoushan')
                ->name('10m.map');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Actions / API-like routes (存檔/刪除/PDF 等)
    |--------------------------------------------------------------------------
    | 統一：/admin/{site}/{module}/actions/...
    | （原本 fstree/fsseedling/fsseeds/ssPlot 全部收進來）
    */

    // ---------------------
    // Fushan - Tree actions
    // ---------------------
    Route::prefix('fushan/tree')->middleware('scope:fushan,tree')->name('fushan.tree.')->group(function () {

        // PDF
        // 舊：GET /fstree/record-pdf/{qx}/{qy}/{type}
        Route::get('/pdf/record/{qx}/{qy}/{type}', [TreePDFController::class, 'record'])
            ->name('pdf.record');

        // Delete / Save
        // 舊：GET /fstree/deletedata/{stemid}/{entry}/{thispage}
        Route::delete('/data/{stemid}/{entry}/{thispage}', [TreeSaveController::class, 'deletedata'])
            ->name('data.delete');

        // 舊：POST /fstree/savedata
        Route::post('/data', [TreeSaveController::class, 'savedata'])
            ->name('data.save');

        // 舊：POST /fstree/saverecruit
        Route::post('/recruit', [TreeSaveController::class, 'saverecruit'])
            ->name('recruit.save');

        // alternote
        // 舊：GET /fstree/addalternote/{stemid}/{entry}/{thispage}
        Route::get('/alternote/{stemid}/{entry}/{thispage}', [TreeAlternote::class, 'alternote'])
            ->name('alternote.form');

        // 舊：POST /fstree/savealternote
        Route::post('/alternote', [TreeSaveController::class, 'savealternote'])
            ->name('alternote.save');

        // update / alter delete
        // 舊：POST /fstree/update
        Route::post('/update', [TreeSaveController::class, 'saveupdate'])
            ->name('update');

        // 舊：GET /fstree/deletealter/{stemid}/{entry}/{thispage}
        Route::delete('/alter/{stemid}/{entry}/{thispage}', [TreeSaveController::class, 'deletealter'])
            ->name('alter.delete');

        // finish
        // 舊：GET /fstree/finish/{qx}/{qy}/{entry}
        Route::post('/finish/{qx}/{qy}/{entry}', [TreeSaveController::class, 'finishnote'])
            ->name('finish');

        // bulk ops
        // 舊：POST /fstree/deletecensusdata
        Route::post('/census/delete', [TreeSaveController::class, 'deleteCensusData'])
            ->name('census.delete');

        // 舊：POST /fstree/adddata
        Route::post('/adddata', [TreeSaveController::class, 'addData'])
            ->name('adddata');
    });

    // -------------------------
    // Fushan - Seedling actions
    // -------------------------
    Route::prefix('fushan/seedling')->middleware('scope:fushan,seedling')->name('fushan.seedling.')->group(function () {

        // PDF
        // 舊：GET /fsseedling/record-pdf/{start}/{end}
        Route::get('/pdf/record/{start}/{end}', [SeedlingPDFController::class, 'record'])
            ->name('pdf.record');

        // 舊：GET /fsseedling/compare-pdf
        Route::get('/pdf/compare', [SeedlingPDFController::class, 'compare'])
            ->name('pdf.compare');

        // save
        // 舊：POST /fsseedling/savecov
        Route::post('/cov', [SeedlingSaveController::class, 'savecov'])
            ->name('cov.save');

        // 舊：POST /fsseedling/savedata
        Route::post('/data', [SeedlingSaveController::class, 'savedata'])
            ->name('data.save');

        // 舊：POST /fsseedling/saverecruit
        Route::post('/recruit', [SeedlingSaveController::class, 'saverecruit'])
            ->name('recruit.save');

        // 舊：POST /fsseedling/saveslroll/{entry}/{trap}
        Route::post('/slroll/{entry}/{trap}', [SeedlingSaveController::class, 'saveslroll'])
            ->name('slroll.save');

        // delete
        // 舊：GET /fsseedling/deletedata/{tag}/{entry}/{thispage}
        Route::delete('/data/{tag}/{entry}/{thispage}', [SeedlingSaveController::class, 'deletedata'])
            ->name('data.delete');

        // 舊：GET /fsseedling/deleteslroll/{tag}/{id}/{entry}/{trap}
        Route::delete('/slroll/{tag}/{id}/{entry}/{trap}', [SeedlingSaveController::class, 'deleteslroll'])
            ->name('slroll.delete');

        // alternote
        // 舊：GET /fsseedling/addalternote/{tag}/{entry}/{thispage}
        Route::get('/alternote/{tag}/{entry}/{thispage}', [SeedlingAlternote::class, 'alternote'])
            ->name('alternote.form');

        // 舊：POST /fsseedling/savealternote
        Route::post('/alternote', [SeedlingSaveController::class, 'savealternote'])
            ->name('alternote.save');

        // delete alter
        // 舊：GET /fsseedling/deletealter/{stemid}/{entry}/{thispage}
        Route::delete('/alter/{stemid}/{entry}/{thispage}', [SeedlingSaveController::class, 'deletealter'])
            ->name('alter.delete');

        // finish
        // 舊：GET /fsseedling/finish/{entry}
        Route::post('/finish/{entry}', [SeedlingSaveController::class, 'finishnote'])
            ->name('finish');
    });

    // ----------------------
    // Fushan - Seeds actions
    // ----------------------
    Route::prefix('fushan/seeds')->middleware('scope:fushan,seeds')->name('fushan.seeds.')->group(function () {

        // 舊：POST /fsseeds/savedata/{type}
        Route::post('/data/{type}', [SeedsSaveController::class, 'savedata'])
            ->name('data.save');

        // 舊：POST /fsseeds/savedata1/{type}
        Route::post('/data1/{type}', [SeedsSaveController::class, 'savedata1'])
            ->name('data1.save');

        // 舊：GET /fsseeds/deletedata/{id}/{info}/{thispage}/{type}
        Route::delete('/data/{id}/{info}/{thispage}/{type}', [SeedsSaveController::class, 'deletedata'])
            ->name('data.delete');

        // 舊：GET /fsseeds/finish
        Route::post('/finish', [SeedsSaveController::class, 'finishnote'])
            ->name('finish');
    });

    // -------------------------
    // Shoushan - Plot actions
    // -------------------------
    Route::prefix('shoushan/tree')->middleware('scope:shoushan,tree')->name('shoushan.tree.')->group(function () {

        // PDF
        // 舊：GET /ssPlot/10m-record-pdf/{plot}
        Route::get('/pdf/10m/record/{plot}', [S10mTreePDFController::class, 'record'])
            ->name('pdf.10m.record');

        // 舊：GET /ssPlot/1ha-record-pdf/{qx}/{qy}
        Route::get('/pdf/1ha/record/{qx}/{qy}', [S1haPDFController::class, 'record'])
            ->name('pdf.1ha.record');

        // save
        // 舊：POST /ssPlot/saveenvi
        Route::post('/envi', [PlotSaveController::class, 'saveenvi'])
            ->name('envi.save');

        // 舊：POST /ssPlot/savedata
        Route::post('/data', [PlotSaveController::class, 'savedata'])
            ->name('data.save');

        // 舊：POST /ssPlot/saverecruit
        Route::post('/recruit', [PlotSaveController::class, 'saverecruit'])
            ->name('recruit.save');

        // delete / alternote / alter delete
        // 舊：GET /ssPlot/deletedata/{stemid}/{entry}/{plotType}/{thispage}
        Route::delete('/data/{stemid}/{entry}/{plotType}/{thispage}', [PlotSaveController::class, 'deletedata'])
            ->name('data.delete');

        // 舊：GET /ssPlot/alternote/{stemid}/{entry}/{plotType}/{thispage}
        Route::get('/alternote/{stemid}/{entry}/{plotType}/{thispage}', [PlotAlternote::class, 'alternote'])
            ->name('alternote.form');

        // 舊：POST /ssPlot/savealternote
        Route::post('/alternote', [PlotSaveController::class, 'savealternote'])
            ->name('alternote.save');

        // 舊：GET /ssPlot/deletealter/{stemid}/{entry}/{plotType}/{thispage}
        Route::delete('/alter/{stemid}/{entry}/{plotType}/{thispage}', [PlotSaveController::class, 'deletealter'])
            ->name('alter.delete');

        // update / delete census
        // 舊：POST /ssPlot/update
        Route::post('/update', [PlotSaveController::class, 'saveupdate'])
            ->name('update');

        // 舊：POST /ssPlot/deletecensusdata
        Route::post('/census/delete', [PlotSaveController::class, 'deleteCensusData'])
            ->name('census.delete');

        // 10m cov
        // 舊：POST /ssPlot/10msaveaddcov
        Route::post('/10m/cov/add', [PlotSaveController::class, 'saveaddcov'])
            ->name('10m.cov.add');

        // 舊：GET /ssPlot/10mdeletecov/{id}/{entry}
        Route::delete('/10m/cov/{id}/{entry}', [PlotSaveController::class, 'deletecov'])
            ->name('10m.cov.delete');

        // 舊：POST /ssPlot/10msavecov
        Route::post('/10m/cov', [PlotSaveController::class, 'savecov'])
            ->name('10m.cov.save');
    });
});
