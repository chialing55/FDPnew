<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
// use Illuminate\Database\Schema\Blueprint;
// use Illuminate\Support\Facades\Schema;
// use Illuminate\Support\Facades\Input;

use App\Models\FsBaseLogin;

//選擇工作項目
class ChoiceController extends Controller
{


    public function check(Request $request){
            // $value = $request->session()->get('check');
            // $value = Session::get('check', function() { return 'no'; });
        // echo '1';
            $user = $request->session()->get('user', function () {
                return 'no';
            });

            if ($user!='no'){
                return view('choice',[
                  'user' => $user
                ]);               
            } else {
                return view('login1', [
                'check' => 'no'
                ]);
            }



    }




}
