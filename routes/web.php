<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\Web\WebIndexController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

Route::get('/locale/{locale}', function ($locale) {
    $availableLocales = ['zh-TW', 'en'];

    if (! in_array($locale, $availableLocales)) {
        $locale = config('app.locale'); // 回到預設
    }

    // 寫入 Session，之後 Middleware 會負責 setLocale
    Session::put('locale', $locale);

    // 回上一頁，沒有就回首頁
    return redirect()->back();
})->name('locale.switch');
// Route::get('/', function () {    
//     return view('login1');
// });

Route::view('/', 'webindex')->name('webindex');

// 首頁（給一般使用者看的）
// Route::get('/', [WebIndexController::class, 'splist'])->name('front.home'); 

// Route::get('/latest-updates', [UpdateController::class, 'latestUpdates'])
//     ->name('latest-updates');

require __DIR__.'/web_admin_legacy.php'; // 舊後台
require __DIR__.'/web_public.php';       // 新前台



