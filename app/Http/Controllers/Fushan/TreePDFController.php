<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;
use PDF;

use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsBaseTreeSplist;

//產生pdf紀錄紙

class TreePDFController extends Controller
{
    public function record(Request $request, $qx, $qy, $type){
        // echo '1';
        //從base裡挑資料
        $input=$request->session()->all();

        $treelist=FsTreeBase::where('qx','like',$qx)->where('qy', 'like', $qy)->where('deleted_at', 'like', '')->orderBy('tag', 'asc')->get()->toArray();
        $treelistR=FsTreeBaseR::where('qx','like',$qx)->where('qy', 'like', $qy)->where('deleted_at', 'like', '')->orderBy('stemid', 'asc')->get()->toArray();

        if (!isset($input['splist'])){
            $splists=FsBaseTreeSplist::select('spcode', 'csp')->get()->toArray();
            foreach($splists as $splist1){
                $splist[$splist1['spcode']]=$splist1['csp'];
            }
            $request->session()->put('splist', $splist);
            // echo '1';
        } else {
            $splist=$input['splist'];
            // echo '2';
            
        }

        foreach ($treelist as $list){
            $taglistqx[$list['tag']]=$list;
            $taglist[]=$list['tag'];
        }

        $stemidNeedAddR=[];

        foreach ($treelistR as $list){
            $taglistqxR[$list['stemid']]=$list;
            $stemidlistR[]=$list['stemid'];
            if (!in_array($list['tag'], $taglist)) {
                $stemidNeedAddR[]=$list['stemid'];
            } 
        }

        //最大桫欏號

        $maxG=end($taglist);

        if ($maxG[0]!='G'){
            $maxG='';
        }
//排除status=0
        $treedatas1=FsTreeCensus4::whereIn('tag', $taglist)->where('status','not like', '0')->where('date', 'not like', '0000-00-00')->where('deleted_at', 'like', '')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get()->toArray();
        if ($stemidNeedAddR!=[]){
            $treedatas2=FsTreeCensus4::whereIn('stemid', $stemidNeedAddR)->where('status','not like', '0')->where('date', 'not like', '0000-00-00')->where('deleted_at', 'like', '')->orderBy('tag', 'asc')->orderBy('branch', 'asc')->get()->toArray();
        } else {
            $treedatas2=[];
        }
        

        if ($treedatas2!=[]){
            $treedatas = array_merge($treedatas1, $treedatas2);
        } else {
            $treedatas = $treedatas1;
        }
        

        $totalnum=0;
        for($i=0;$i<count($treedatas);$i++){
            //排除分支的status為-3, -2, -1
            if($treedatas[$i]['branch']!='0' && $treedatas[$i]['status']=='-3')continue;
            if($treedatas[$i]['branch']!='0' && $treedatas[$i]['status']=='-2')continue;
            if($treedatas[$i]['branch']!='0' && $treedatas[$i]['status']=='-1')continue;
            if ($treedatas[$i]['status']=='-9'){
                $treedatas[$i]['status']='';
            }
            //census3=-1的就不要了//
            // 若census3還在，將census3的code填入(有code的話)
            if ($treedatas[$i]['status']=='-1'){
                $missdata=FsTreeCensus3::where('tag', 'like', $treedatas[$i]['tag'])->where('status', 'like', '-1')->get();
                if (!$missdata->isEmpty()){
                    continue;
                } else {
                    $missdata1=FsTreeCensus3::where('tag', 'like', $treedatas[$i]['tag'])->get();
                    if ($treedatas[$i]['tag'][0]!='G'){
                        $treedatas[$i]['dbh']='('.$missdata1[0]['dbh'].")";
                    } else {
                        $treedatas[$i]['h2']='('.$missdata1[0]['h2'].")";
                    }
                    if ($missdata1[0]['code']!=''){
                        $treedatas[$i]['code']='('.$missdata1[0]['code'].")";
                    }
                    
                }

            }


            if (isset($taglistqx[$treedatas[$i]['tag']])){
                $treedatas[$i]['spcode']=$taglistqx[$treedatas[$i]['tag']]['spcode'];
            } else {
                $treedatas[$i]['spcode']=$taglistqxR[$treedatas[$i]['stemid']]['spcode'];
            }
            
            $treedatas[$i]['csp']=$splist[$treedatas[$i]['spcode']];
            
            //code=c, 移除code資料
            if ($treedatas[$i]['code']=='C'){
                $treedatas[$i]['code']='';
            }

            if ($treedatas[$i]['pom']!='1.3'){
                $treedatas[$i]['note']='[POM = '.$treedatas[$i]['pom']."] ".$treedatas[$i]['note'];
            }



            if (strpos($treedatas[$i]['code'], 'R') !== false){   //code包含R
                if (in_array($treedatas[$i]['stemid'], $stemidlistR)){
                    $sqx=$taglistqxR[$treedatas[$i]['stemid']]['sqx'];
                    $sqy=$taglistqxR[$treedatas[$i]['stemid']]['sqy'];
                    //找主幹的位置
                    $treelisttemp=FsTreeBase::where('tag', 'like', $treedatas[$i]['tag'])->where('deleted_at', 'like', '')->first();
                    if ($treelisttemp) {
                        $treelisttemp = $treelisttemp->toArray();
                        if ($treelisttemp['qx'].$treelisttemp['qy'] == $taglistqxR[$treedatas[$i]['stemid']]['qx'].$taglistqxR[$treedatas[$i]['stemid']]['qy']){
                            $treedatas[$i]['note'].=' 主幹位於('.$treelisttemp['sqx'].', '.$treelisttemp['sqy'].')';
                        } else {
                            $treedatas[$i]['note'].=' 主幹位於('.$treelisttemp['qx'].', '.$treelisttemp['qy'].')('.$treelisttemp['sqx'].', '.$treelisttemp['sqy'].')';
                        }
                        
                    }
                } else {
                    //可能不在這個20*20
                    $treelistR_1=FsTreeBaseR::where('stemid','like',$treedatas[$i]['stemid'])->where('deleted_at', 'like', '')->first();
                    if ($treelistR_1) {
                        $treelistR_1 = $treelistR_1->toArray();
                        $treedatas[$i]['note'].=' 位於('.$treelistR_1['qx'].', '.$treelistR_1['qy'].')('.$treelistR_1['sqx'].', '.$treelistR_1['sqy'].')';
                    }
                }

            } else {
                $sqx=$taglistqx[$treedatas[$i]['tag']]['sqx'];
                $sqy=$taglistqx[$treedatas[$i]['tag']]['sqy'];
            }




            if ($treedatas[$i]['branch']=='0'){
                $maxb=FsTreeCensus4::where('tag', 'like', $treedatas[$i]['tag'])->orderBy('branch','desc')->get();
                // print_r($maxb[0]);
                if ($maxb[0]['branch']!='0'){
                    $treedatas[$i]['maxb']=$maxb[0]['branch'];
                }
            }
            //每小區的植株資料->共有幾筆
            $datasqx[$sqx][$sqy][]=$treedatas[$i];  
            //每小區植株的編號列表->共有幾棵樹
            $datatagsqx[$sqx][$sqy][]=$treedatas[$i]['tag']; 
            $totalnum=$totalnum+1; 

        }


        for($x=1;$x<5;$x++){
            for($y=1;$y<5;$y++){
                if (isset($datatagsqx[$x][$y])){
                    $datatagsqx[$x][$y]=array_unique($datatagsqx[$x][$y]);
                }
            }
        }
        // print_r($splist);
        // print_r($datasqx[2][2]);
        // echo "(2,2) 共".count($datasqx[2][2])."筆/".count($datatagsqx[2][2])."棵樹";
        // echo count($treedatas)."<br>";
        // echo $totalnum."<br>";
// (18,14)-> 335/10  (16,4)->255/7
        //每行約0.6cm，(1,1)那一行約0.9cm，0.9*16(16個樣區行)/0.6=24
        $totalpage = round(($totalnum+24) / 39) ;
        // print_r($splist);
        // echo $totalpage;
            
            $data = [
                'title' => date('Y').' 年福山樣區第 5 次每木調查',
                'filename' =>'fs_record_'.$qx.$qy,
                'datatagsqx' => $datatagsqx,
                'datasqx' => $datasqx,
                'totalpage' => $totalpage,
                'qx' => $qx,
                'qy' => $qy,
                'maxG' => $maxG
            ];


            $pdf = PDF::loadView('pages.fushan.tree_record', $data)->setPaper('A4');
            // $pdf ->set_option( 'isFontSubsettingEnabled' , true );
            if ($type==1){  //有qx及qy
        // return $dompdf->output();
            return $pdf->stream($data['filename'].".pdf");   //在網頁顯示
            // return $pdf->download($data['title'].".pdf");//直接下載     
            // return $pdf;

            // return view('includes.fushan.tree_record', $data);
           

            } else {
                //全線下載
            
            $directory = public_path('recordpdf/'.$data['qx']);
            $filename = $data['filename'] . '.pdf';

            // 檢查資料夾是否存在，如果不存在則建立新資料夾
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }


            // 完整的檔案路徑
            $filepath = $directory . '/' . $filename;

            // 將 PDF 存儲到指定資料夾中
            // Storage::put($filepath, $pdf->output());
            // 將 PDF 存儲到指定資料夾中
            // file_put_contents($filepath, $pdf->output());
            // 返回儲存檔案的路徑
            if (!file_exists($filepath)) {
                // 將 PDF 存儲到指定資料夾中
                file_put_contents($filepath, $pdf->output());
            }


            return $filepath;
            }

    }




}
