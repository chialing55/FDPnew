<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;


class FsSeedsController extends Controller
{


    public function seeds(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {

            return view('pages/fushan/seeds_doc', [
                'site' => $site,
                'project' => '種子雨',
                'user' => $user


            ]);
        }
    }


    public function entry(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {

            return view('pages/fushan/seeds_entry', [
                'site' => $site,
                'project' => '種子雨',
                'user' => $user

            ]);
        }
    }


    public function import(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {


            return view('pages/fushan/seeds_import', [
                'site' => $site,
                'project' => '種子雨',
                'user' => $user

            ]);
        }
    }


    public function note(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {


            return view('pages/fushan/seeds_note', [
                'site' => $site,
                'project' => '種子雨',
                'user' => $user
                

            ]);
        }
    }


    public function showdata(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {


            return view('pages/fushan/seeds_dataviewer', [
                'site' => $site,
                'project' => '種子雨',
                'user' => $user
                

            ]);
        }
    }

    public function unknown(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {


            return view('pages/fushan/seeds_unknown', [
                'site' => $site,
                'project' => '種子雨',
                'user' => $user
                

            ]);
        }
    }

    public function updateBackData(Request $request, $site){

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        if ($user=='no'){
            return view('login1', [
                'check' => 'no'
            ]);
        } else {


            return view('pages/fushan/seeds_updatebackdata', [
                'site' => $site,
                'project' => '種子雨',
                'user' => $user
                

            ]);
        }
    }


}
