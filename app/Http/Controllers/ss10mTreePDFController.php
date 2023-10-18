<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;


use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;
use PDF;
use App\Models\Ss10mQuad2014;
use App\Models\Ss10mTree2014;
use App\Models\Ss10mTree2015;


class ss10mTreePDFController extends Controller
{
    public function record(Request $request, $plot){
        // echo '1';
        //從base裡挑資料
        $input=$request->session()->all();

        $plotinfo=Ss10mQuad2014::where('plot_2023','like',$plot)->get()->toArray();

        $treedatas=Ss10mTree2015::where('plot','like',$plot)->where('status', 'not like','0')->where('status', 'not like','-3')->get()->toArray();
        if (count($treedatas)==0){
            $treedatas=Ss10mTree2014::where('plot','like',$plot)->get()->toArray();
        }



        
        // print_r($treedata);
        for($i=0;$i<count($treedatas);$i++){

            if ($treedatas[$i]['b']=='0'){
                $maxb=Ss10mTree2015::where('tag','like',$treedatas[$i]['tag'])->where('plot','like',$plot)->orderBy('b','desc')->get()->toArray();
                // print_r($maxb[0]);
                if (count($maxb)==0){
                    $maxb=Ss10mTree2014::where('tag','like',$treedatas[$i]['tag'])->where('plot','like',$plot)->orderBy('b','desc')->get()->toArray();
                }

                if ($maxb[0]['b']!='0'){
                    $treedatas[$i]['maxb']=$maxb[0]['b'];
                }
            }

            if ($treedatas[$i]['b']=='0'){
                $datax5tag[$treedatas[$i]['x5']][$treedatas[$i]['y5']][]=$treedatas[$i]['tag'];
            }
            
            $datax5[$treedatas[$i]['x5']][$treedatas[$i]['y5']][]=$treedatas[$i];
        }



        // 每行約0.6cm，(1,1)那一行約0.9cm，0.9*4(4個樣區行)/0.6=6
        //環境資料約3.2公分 3.2/0.6=5.3
        $totalpage=ceil((count($treedatas)+11)/39);
// echo count($treedatas);
            
            $data = [
                'title' => '2023 壽山地區零散森林樣區 複查',
                'filename' =>'ss_10m_'.$plot,
                'plotinfo' => $plotinfo[0],
                'datatagsqx' => $datax5tag,
                'datasqx' => $datax5,
                'totalpage' => $totalpage,
                'plot' => $plot,

            ];

// print_r($data);

            $pdf = PDF::loadView('pages.shoushan.ss10mf_record', $data)->setPaper('A4');
            // $pdf ->set_option( 'isFontSubsettingEnabled' , true );
        // return $dompdf->output();
            return $pdf->stream($data['filename'].".pdf");   //在網頁顯示
            // return $pdf->download($data['title'].".pdf");//直接下載     
            // return $pdf;

            // return view('includes.fushan.tree_record', $data);



    }




}
