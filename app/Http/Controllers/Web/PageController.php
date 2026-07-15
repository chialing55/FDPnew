<?php

namespace App\Http\Controllers\Web;

use App\Models\Web\Page;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::with(['site', 'subject'])->where('slug', $slug)->firstOrFail();

        if ($page->nav_group === 'sites') {
            abort_unless($page->site?->is_active, 404);
        }

        if ($page->nav_group === 'subjects') {
            abort_unless($page->subject?->is_active, 404);
        }

        $view = $page->view_name ?? 'pages.web.default';

        return view($view, [
            'slug' => $slug,
            'page' => $page,
        ]);        
    }
}
