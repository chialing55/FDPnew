<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\WebIndexController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ResearchOutputController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\NewsController;

Route::prefix('web')->group(function () {
    Route::redirect('/splist', '/splist', 301)->name('front.splist.legacy');
    Route::get('/species/{spcode}', [WebIndexController::class, 'species'])->name('front.species.legacy');
});

Route::get('/splist', [WebIndexController::class, 'splist'])->name('front.splist');
Route::redirect('/plants', '/splist', 301);


// === 前台公開頁面 ===


// 你也可以另外做一個 index() 畫面，先用 splist 當首頁也可以

// // 單一物種頁
// Route::get('/species/{spcode}', [WebIndexController::class, 'species'])->name('front.species');


// 成果首頁（可先用 Page 或獨立 controller）
Route::get('results', [PageController::class, 'show'])
    ->defaults('slug', 'results')
    ->name('results.index');
    
// Route::get('results', [ResearchOutputController::class, 'index'])
//     ->name('results.index');

// 單一成果頁（ResearchOutput）
Route::get('results/{slug}', [ResearchOutputController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('research_outputs.show');

Route::get('projects/{project}', [ProjectController::class, 'show'])
    ->whereNumber('project')
    ->name('projects.show');

Route::get('about/news/{news}', [NewsController::class, 'show'])
    ->whereNumber('news')
    ->name('news.show');

// Research subject URLs changed in 2026. Keep published links working.
Route::redirect('subjects/seedling', '/subjects/forest-regeneration', 301);
Route::redirect('subjects/understory', '/subjects/plant-diversity-community', 301);
Route::redirect('subjects/epiphytes', '/subjects/plant-diversity-community', 301);
Route::redirect('subjects/seeds', '/subjects/plant-reproduction-phenology', 301);
Route::redirect('subjects/mortality', '/subjects/tree', 301);
Route::redirect('subjects/functionaltraits', '/subjects/functional-traits', 301);



//通用版要放在最後面
Route::get('{slug}', [\App\Http\Controllers\Web\PageController::class, 'show'])
    ->where('slug', '^(?!login$|register$|logout$|forgot-password$|reset-password$|confirm-password$|verify-email$|email$|profile$|cms$|admin$).+')
    ->name('pages.show');



//background

// Route::view('/background/motivation', 'pages.web.background-motivation')->name('background.motivation');
// Route::view('/background/team', 'pages.web.background-team')->name('background.team');
// Route::view('/background/partners', 'pages.web.background-partners')->name('background.partners');
// Route::view('/background/taiwanplots', 'pages.web.background-taiwanplots')->name('background.taiwanplots');

// //plots
// Route::view('/plots/fushan', 'pages.web.plots-fushan')->name('plots.fushan');
// Route::view('/plots/nanjenshan', 'pages.web.plots-nanjenshan')->name('plots.nanjenshan');
// Route::view('/plots/shoushan', 'pages.web.plots-shoushan')->name('plots.shoushan');

// //subjects
// Route::view('/subjects/tree', 'pages.web.subjects-tree')->name('subjects.tree');
// Route::view('/subjects/seedling', 'pages.web.subjects-seedling')->name('subjects.seedling');
// Route::view('/subjects/seeds', 'pages.web.subjects-seeds')->name('subjects.seeds'); 
// Route::view('/subjects/mortality', 'pages.web.subjects-mortality')->name('subjects.mortality');
// Route::view('/subjects/functionaltraits', 'pages.web.subjects-functionaltraits')->name('subjects.functionaltraits');
// Route::view('/subjects/canopy', 'pages.web.subjects-canopy')->name('subjects.canopy');
// Route::view('/subjects/epiphytes', 'pages.web.subjects-epiphytes')->name('subjects.epiphytes');

// //plants
// Route::view('/plants', 'pages.web.plants')->name('plants.index');
// //news
// Route::view('/news', 'pages.web.news')->name('news.index');
