<?php

namespace App\Http\Controllers\Web;

use App\Models\Web\Page;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->firstOrFail();

        $view = $page->view_name ?? 'pages.web.default';

        return view($view, [
            'slug' => $slug,
            'page' => $page,
        ]);        
    }
}
