<?php

namespace App\Http\Controllers\Nanjenshan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SeedlingController extends Controller
{
    public function doc(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/nanjenshan/seedling_doc', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->name,
        ]);
    }

    public function dataviewer(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');

        return view('pages/nanjenshan/seedling_dataviewer', [
            'site' => $site,
            'project' => '小苗',
            'user' => $user->name,
        ]);
    }
}
