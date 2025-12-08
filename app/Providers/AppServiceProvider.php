<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use App\Models\Web\Page;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {

        View::composer('includes.web-navigation', function ($view) {
            $aboutPages = Page::where('nav_group', 'about')
                ->orderBy('nav_order')
                ->get();

            $plotPages = Page::where('nav_group', 'plots')
                ->orderBy('nav_order')
                ->get();

            $subjectPages = Page::where('nav_group', 'subjects')
                ->orderBy('nav_order')
                ->get();

            $resultPages = Page::where('nav_group', 'results')
                ->orderBy('nav_order')
                ->get();

            $view->with([
                'navAboutPages' => $aboutPages,
                'navPlotPages'       => $plotPages,
                'navSubjectPages'    => $subjectPages,
                'navResultsPages'    => $resultPages,
            ]);
        });

    }
}
