<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\Web\WebIndexController;
use App\Http\Controllers\ContentImageController;
use App\Http\Controllers\ChangYangController;

Route::prefix('changyang')->name('changyang.')->group(function () {
    Route::get('/', [ChangYangController::class, 'show'])->name('home');
    Route::get('/{page}.html', [ChangYangController::class, 'legacy'])
        ->where('page', '[a-z0-9-]+')
        ->name('legacy');
    Route::get('/{page}', [ChangYangController::class, 'show'])
        ->where('page', '[a-z0-9-]+')
        ->name('page');
});

/*
|--------------------------------------------------------------------------
| 語系切換
|--------------------------------------------------------------------------
*/
Route::get('/locale/{locale}', function ($locale) {
    $availableLocales = ['zh-TW', 'en'];

    if (! in_array($locale, $availableLocales)) {
        $locale = config('app.locale');
    }

    Session::put('locale', $locale);

    return redirect()->back();
})->name('locale.switch');

/*
|--------------------------------------------------------------------------
| 前台首頁（你原本的）
|--------------------------------------------------------------------------
*/
Route::view('/', 'webindex')->name('webindex');
// 或如果你之後要用 controller：
// Route::get('/', [WebIndexController::class, 'splist'])->name('front.home');

/*
|--------------------------------------------------------------------------
| Breeze / Laravel Auth 相關（只留必要）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'approved'])->group(function () {
    Route::post('/cms/content-images', [ContentImageController::class, 'store'])
        ->name('cms.content-images.store');
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 其他 routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';             // Breeze 產生（login / logout / register）
require __DIR__.'/web_admin_legacy.php'; // 舊後台
require __DIR__.'/web_public.php';       // 新前台
