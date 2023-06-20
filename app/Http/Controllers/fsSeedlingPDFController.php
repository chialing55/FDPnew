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

        $slplot=FsSeedlingData::select('trap', 'plot')->whereBetween('trap', [$start, $end])->groupBy('trap', 'plot')->get();
        for($i=0; $i<count($slplot);$i++){
            $slplot1[]=$slplot[$i]['trap']."-".$slplot[$i]['plot'];
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
        $totalpage=ceil($length/37);
        // print_r($slrecord2);
        if ($length>750){
            return redirect()->back() ->with('alert', '資料過多，請重新選擇範圍');

        } else {

            if ($slrecord[0]['census']%2==0){
                $month='八';
            } else { $month = '二';}
            
            $data = [
                'title' => date('Y').' 年'.$month.'月第 '.$slrecord[0]['census'].' 次福山喬木小苗調查 ('.$start."-".$end.")",
                'record' => $slrecord2,
                'plot' => $slplot1,
                'numPagesTotal' => $totalpage,
                'start' => $start,
                'end' => $end
            ];


            $pdf = PDF::loadView('pages.fushan.seedling_record', $data)->setPaper('A4', 'landscape');
            $pdf ->set_option( 'isFontSubsettingEnabled' , true );

        // return $dompdf->output();
            return $pdf->stream($data['title'].".pdf");   
            // return $pdf->download($data['title'].".pdf");      
            // return $pdf;

            // return view('includes.fushan.seedling_record', $data);

        }


    }


}
