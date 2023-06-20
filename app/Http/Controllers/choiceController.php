<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;

use App\Models\FsBaseLogin;


class choiceController extends Controller
{


    public function check(Request $request){
            // $value = $request->session()->get('check');
            // $value = Session::get('check', function() { return 'no'; });
        // echo '1';
            $value = $request->session()->get('user', function () {
                return 'no';
            });

            if ($value!='no'){
                return view('choice',[
                  'user' => $value
                ]);               
            } else {
                return view('login', [
                'check' => 'no'
                ]);
            }



    }




}
