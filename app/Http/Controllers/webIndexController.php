<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
// use Illuminate\Support\Facades\Input;

// use App\Models\FsBaseLogin;
use App\Http\Controllers\UpdateController;

class WebIndexController extends Controller
{


    public function index(Request $request){
        // Session::start();
        // $input = Request::all();
        $lasterUpdate='';
        $ob_update = new updateController;
        $lasterUpdate=$ob_update->latestUpdates();
      
        $request->session()->put('latest_update', $lasterUpdate);


         return view('webindex');


    }

    public function species(Request $request){

         return view('web/species',[
            'spcode' => $spcode
         ]);


    }


    // public function splist(Request $request){



    //     return view('pages/web/splist');


    // }

}
