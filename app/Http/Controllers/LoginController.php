<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
// use Illuminate\Support\Facades\Input;

use App\Models\FsBaseLogin;
use App\Http\Controllers\UpdateController;

//登入處理
class LoginController extends Controller
{


    public function login(Request $request){
        // Session::start();
        // $input = Request::all();
        $lasterUpdate='';
        $ob_update = new UpdateController;
        $lasterUpdate=$ob_update->latestUpdates();
      
        $request->session()->put('latest_update', $lasterUpdate);

            $input = request()->all();
            // print_r($input);
        if (!isset($input)){
            $input=array('id'=>'', 'pass'=>'');
        }
        // echo "2";
        // print_r($input);
        // echo "</br>";

        if ($input['id']==''){
            $input['id']='0';
        }
        if ($input['pass']==''){
            $input['pass']='0';
        }
        // echo "1";
        // print_r($input);
        // echo "</br>";

        $ident_id=FsBaseLogin::where('id2', 'like', $input['id'])->where('pass', 'like', $input['pass'])->get();
        // echo "3";
        // print_r($ident_id);
        // echo "</br>";        
        if (!$ident_id->isEmpty()){

            $request->session()->put('user', $input['id']);


            return redirect()->to('choice')->send();

        } else {
            // echo 'no';

            return view('/login1', [
                'check' => 'no'
            ]);
        }


    }




}
