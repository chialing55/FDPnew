<?php

namespace App\Http\Controllers\Shoushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;
use PDF;

use App\Models\Ss1haBase2015;
use App\Models\Ss1haData2015;

//產生一公頃樣區紀錄紙

class S1haPDFController extends Controller
{
    public function record(Request $request, $qx, $qy){
        // echo '1';
        //從base裡挑資料
        $input=$request->session()->all();

        // $plotinfo=Ss10mQuad2014::where('plot_2023','like',$plot)->get()->toArray();

        $treedatas=Ss1haData2015::where('qx','like',$qx)->where('qy','like',$qy)->where('status', 'not like','0')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get()->toArray();



        
        // print_r($treedata);
        for($i=0;$i<count($treedatas);$i++){


            if ($treedatas[$i]['branch']=='0'){
                $maxb=Ss1haData2015::where('tag','like',$treedatas[$i]['tag'])->where('qx','like',$qx)->where('qy','like',$qy)->orderBy('branch','desc')->get()->toArray();
                // print_r($maxb[0]);
                // if (count($maxb)==0){
                //     $maxb=Ss10mTree2014::where('tag','like',$treedatas[$i]['tag'])->where('plot','like',$plot)->orderBy('branch','desc')->get()->toArray();
                // }

                if ($maxb[0]['branch']!='0'){
                    $treedatas[$i]['maxb']=$maxb[0]['branch'];
                }
            }

            if ($treedatas[$i]['branch']=='0'){
                $datasqxtag[$treedatas[$i]['sqx']][$treedatas[$i]['sqy']][]=$treedatas[$i]['tag'];
            }
            
            $datasqx[$treedatas[$i]['sqx']][$treedatas[$i]['sqy']][]=$treedatas[$i];
        }



        // 每行約0.6cm，(1,1)那一行約0.9cm，0.9*4(4個樣區行)/0.6=6
        //環境資料約3.2公分 3.2/0.6=5.3
    
        $totalpage=round((count($treedatas)+24)/39);
// echo count($treedatas);
            
            $data = [
                'title' => '2024 壽山 1.05 ha 樣區 複查',
                'filename' =>'ss_1ha_'.$qx.$qy,
                // 'plotinfo' => $plotinfo[0],
                'datatagsqx' => $datasqxtag,
                'datasqx' => $datasqx,
                'totalpage' => $totalpage,
                'qx' => $qx,
                'qy' => $qy

            ];

// print_r($data);

            $pdf = PDF::loadView('pages.shoushan.1ha_record', $data)->setPaper('A4');
            // $pdf ->set_option( 'isFontSubsettingEnabled' , true );
        // return $dompdf->output();
            return $pdf->stream($data['filename'].".pdf");   //在網頁顯示
            // return $pdf->download($data['title'].".pdf");//直接下載     
            // return $pdf;

            // return view('includes.fushan.tree_record', $data);



    }




}
