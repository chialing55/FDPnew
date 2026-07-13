<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use App\Models\Web\Page;

use Illuminate\Database\Eloquent\Relations\Relation;

use App\Models\Web\ResearchOutput;


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

            $sitePages = Page::where('nav_group', 'sites')
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
                'navSitePages'       => $sitePages,
                'navSubjectPages'    => $subjectPages,
                'navResultsPages'    => $resultPages,
            ]);
        });
        Relation::morphMap([
            // key = 資料庫 owner_type 存的字串
            // value = 實際的 Model 類別

            'pages'            => Page::class,
            'research_outputs' => ResearchOutput::class,
            'projects'         => \App\Models\Web\Project::class,

            // 如果你想改成簡短一點也可以：
            // 'page'   => Page::class,
            // 'result' => ResearchOutput::class,
        ]);
    }
}
