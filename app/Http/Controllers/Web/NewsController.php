<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Web\News;

class NewsController extends Controller
{
    public function show(News $news)
    {
        abort_unless($news->is_public, 404);

        return view('pages.web.news-show', compact('news'));
    }
}
