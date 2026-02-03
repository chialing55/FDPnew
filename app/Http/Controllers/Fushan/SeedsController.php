<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;

//分配網址至各頁面

class SeedsController extends Controller
{


    public function seeds(Request $request)
    {
        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_doc', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->name


        ]);
    }


    public function entry(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');



        return view('pages/fushan/seeds_entry', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->name

        ]);
    }


    public function import(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_import', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->name

        ]);
    }


    public function note(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');

        return view('pages/fushan/seeds_note', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->name


        ]);
    }


    public function showdata(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_dataviewer', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->name


        ]);
    }

    public function unknown(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');


        return view('pages/fushan/seeds_unknown', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->name


        ]);
    }

    public function updateBackData(Request $request)
    {

        $user = $request->user();
        $site = $request->route('site');
        return view('pages/fushan/seeds_updatebackdata', [
            'site' => $site,
            'project' => '種子雨',
            'user' => $user->name

        ]);
    }
}
