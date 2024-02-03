<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;
use PDF;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingData;

class fsSeedlingPDFController extends Controller
{
    public function record($start, $end){
        // echo '1';
        $slplot1=array();

        // $slplot=FsSeedlingData::select('trap', 'plot')->whereBetween('trap', [$start, $end])->groupBy('trap', 'plot')->get();
        // for($i=0; $i<count($slplot);$i++){
        //     $slplot1[]=$slplot[$i]['trap']."-".$slplot[$i]['plot'];
        // }

        for ($i=$start; $i<$end+1; $i++){
            for($j=1;$j<4; $j++){
                $slplot1[]=$i."-".$j;
            }
        }

        // print_r($slplot1);
        // $starttp = array_search ($start."-1", $slplot1);
        // $endtp = array_search ($end."-3", $slplot1);

        $slrecord=FsSeedlingSlrecord::whereBetween('trap', [$start, $end])->orderBy('trap','asc')->orderBy('plot','asc')->orderBy('tag','asc')->get();
        for($i=0; $i<count($slrecord);$i++){
            $plot=$slrecord[$i]['trap']."-".$slrecord[$i]['plot'];
            $slrecord[$i]['TP']=$plot;
            $slrecord2[$plot][]=$slrecord[$i];
            
        }
        $length=count($slrecord)+count($slplot1);
        $totalpage=ceil($length/31);
        // print_r($slrecord2);
//最大分支號
        $maxbtable=FsSeedlingData::select('mtag', DB::raw('MAX(CAST(SUBSTRING_INDEX(tag, ".", -1) AS DECIMAL)) AS max_b'))->where('sprout', 'like', 'TRUE')->groupBy('mtag')->get();
                   
            
        $maxb=[];
        for($i=0; $i<count($maxbtable);$i++){
            if ($maxbtable[$i]['max_b']!='0' && $maxbtable[$i]['max_b']<200){
                $maxb[$maxbtable[$i]['mtag']]=$maxbtable[$i]['max_b'];
            }
        }

        if ($length>750){
            return redirect()->back() ->with('alert', '資料過多，請重新選擇範圍');

        } else {

            if ($slrecord[0]['census']%2==1){
                $month='八';
            } else { $month = '二';}
            
            $data = [
                'title' => date('Y').' 年'.$month.'月第 '.$slrecord[0]['census'].' 次福山喬木小苗調查 ('.$start."-".$end.")",
                'record' => $slrecord2,
                'maxb' => $maxb,
                'plot' => $slplot1,
                'numPagesTotal' => $totalpage,
                'start' => $start,
                'end' => $end
            ];


            $pdf = PDF::loadView('pages.fushan.seedling_record', $data)->setPaper('A4', 'landscape');
            $pdf ->set_option( 'isFontSubsettingEnabled' , true );
            // $options = [
            //     'margin-top' => 10,    // 上邊界
            //     'margin-right' => 10,  // 右邊界
            //     'margin-bottom' => 20, // 下邊界
            //     'margin-left' => 10,   // 左邊界
            // ];

            // $pdf->setOptions($options);

        // return $dompdf->output();
            return $pdf->stream($data['title'].".pdf");   
            // return $pdf->download($data['title'].".pdf");      
            // return $pdf;

            // return view('includes.fushan.seedling_record', $data);

        }


    }


    public function compare(Request $request){
        $comnote = $request->session()->get('comnote');
        $html="<p style='font-family: msjh'>".$comnote."</p>";


        
        // print_r($comnote);
        $pdf= PDF::loadHtml($html)->setPaper('A4');
        // $pdf ->set_option( 'isFontSubsettingEnabled' , true );
        return $pdf->stream("seedling_compare.pdf");


    }

}
