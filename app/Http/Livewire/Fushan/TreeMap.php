<?php

namespace App\Http\Livewire\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\File;


use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeRecord1;
use App\Models\FsTreeRecord2;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;
use App\Models\FsTreeCensus5;

use App\Models\FsTreeBase;
use App\Models\FsTreeBaseR;
use App\Models\FsTreeFixlog;


use App\Jobs\FsTreeCensus5Progress;

//樹位置圖輸入

class TreeMap extends Component
{

    public $user;
    public $site;
    public $data;
    public $qx;
    public $qy;
    public $subqx;
    public $subqy;
    public $sqx;
    public $sqy;
    public $record;
    public $datasavenote='';
    public $alternotelist=[];
    public $showdata;

    public $finishMap=[];

    public function mount(){

        $ob_result = new FsTreeCensus5Progress;
        $result=$ob_result->showProgress();
//點圖進度
        $nomap1=FsTreeBase::select(DB::raw('CONCAT(qx, "-", qy) AS qxqy'))->where('plotx', 'like', '0')->where('ploty', 'like', '0')->groupby('qxqy')->pluck('qxqy')->toArray();
        $nomap2=FsTreeBaseR::select(DB::raw('CONCAT(qx, "-", qy) AS qxqy'))->where('plotx', 'like', '0')->where('ploty', 'like', '0')->groupby('qxqy')->pluck('qxqy')->toArray();
        $nomap = array_unique(array_merge($nomap1, $nomap2));

        for($i=0;$i<25;$i++){
            for($j=0;$j<25;$j++){
                if (in_array($i, $result['alternotelist'])){
                    $q=$i.'-'.$j;
                    if (in_array($q, $nomap)){
                        $table["'".$i.'-'.$j."'"]='1';
                    } else {
                        $table["'".$i.'-'.$j."'"]='2';
                    }
                } else {
                    $table["'".$i.'-'.$j."'"]='0';
                }
            }
        }
        $this->finishMap=$table;
        $this->datasavenote='';
        // Extract only the directory names
        $this->alternotelist = $result['alternotelist'];
        // dd($this->directories);
    }


    public function submitForm(Request $request){
        $this->datasavenote='';
        if ($this->qx!=''){
            $this->searchSite($request, $this->qx, $this->qy, 1, 1);
        }

    }

    public function submitsqxForm(Request $request, $subqx, $subqy){
        $this->datasavenote='';

        $this->searchSite($request, $this->qx, $this->qy, $subqx, $subqy);

    }
    public $result;
    public $tablePlot;

    public $dataR;
    public $datanote;
    public $filePath;
    public $tag;
    public $x;
    public $y;

//排序資料，把空白資料放前面

    public function customSort($a, $b) {
        // 检查 $a 和 $b 是否满足条件
        $aCondition = $a['qudx'] == '0' && $a['qudy'] == '0';
        $bCondition = $b['qudx'] == '0' && $b['qudy'] == '0';

        // 如果 $a 满足条件而 $b 不满足条件，将 $a 放在前面
        if ($aCondition && !$bCondition) {
            return -1;  // $a 在前面
        }
        // 如果 $b 满足条件而 $a 不满足条件，将 $b 放在前面
        elseif (!$aCondition && $bCondition) {
            return 1;  // $b 在前面
        }
        // 如果都满足条件或都不满足条件，则按照 'tag' 键排序
        return $a['tag'] <=> $b['tag'];
    }


//選擇點圖樣區
    public function searchSite(Request $request, $qx, $qy, $subqx, $subqy){
            $R='N';

            $data = FsTreeBase::select('base.*', 'census5.status', 'base.updated_at as update_date')->join('census5', 'census5.tag', '=', 'base.tag')->where('census5.date', '!=', '0000-00-00')->where('census5.branch', '==', '0')->where('base.deleted_at', 'like', '')->where('base.qx', 'like', $qx)->where('base.qy', 'like', $qy)->where('base.subqx','like', $subqx)->where('base.subqy', 'like', $subqy)->orderBy('base.qudx')->orderBy('base.qudy')->orderBy('base.tag')->get()->toArray();

            // $dataR=FsTreeBaseR::where('qx','like', $qx)->where('qy', 'like', $qy)->where('subqx','like', $subqx)->where('subqy', 'like', $subqy)->orderBy('tag')->get()->toArray();
            $dataR = FsTreeBaseR::select('base_r.*', 'census5.status', 'base_r.updated_at as update_date')->join('census5', 'census5.stemid', '=', 'base_r.stemid')->where('census5.date', '!=', '0000-00-00')->where('base_r.deleted_at', 'like', '')->where('base_r.qx', 'like', $qx)->where('base_r.qy', 'like', $qy)->where('base_r.subqx','like', $subqx)->where('base_r.subqy', 'like', $subqy)->orderBy('base_r.qudx')->orderBy('base_r.qudy')->orderBy('base_r.stemid')->get()->toArray();
// dd($data);
            foreach ($dataR as &$datar){   //加上&就可以更改$datar
                $datar['tag']=$datar['stemid'];
                $datar['type']="R";
            }

            foreach ($data as &$datae){
                $datae['type']="all";
            }

            $mergedData = array_merge($data, $dataR);
            $result = array_values($mergedData);//重新給key


// 使用 usort 函数对数组进行排序
            usort($result, array($this, 'customSort'));
            // $tag = array_column($result, 'tag');
            // array_multisort($tag, SORT_ASC, $result);
            // dd($result);

            $d = 0;

            foreach ($result as $item) {
                if ($item['qudx'] == '0' && $item['qudy'] == '0') {
                    $d++;
                }
            }


            $datanote='共 '.count($result).' 筆。其中有 '.$d.' 筆缺值';

  
        //     $data=FsTreeBase::query()->join('census5', 'base.tag', '=', 'census5.tag')->where('base.qx','like', $qx)->where('base.qy', 'like', $qy)->where('base.subqx','like', $subqx)->where('base.subqy', 'like', $subqy)->where('qudx', 'like', '0')->where('census5.branch', '=', '0')->where('census5.status', '=', '-9')->orderBy('base.tag')->get()->toArray();

        
// dd($qx);
// dd($result);
        $this->data=$result;
        $this->datanote=$datanote;
        $this->result=$result;
        $this->qx=$qx;
        $this->qy=$qy;
        $this->subqx=$subqx;
        $this->subqy=$subqy;
        $this->showdata='1';
        $this->tablePlot=$qx.$qy.$subqx.$subqy;
        // $this->datasavenote='';
        $this->tag='';
        $this->x='';
        $this->y='';
        $this->showmap();
        // $this->datasavenote='';
        $this->errorList=[];
        $this->dispatchBrowserEvent('initTablesorter', ['tablePlot'=>$this->tablePlot, 'data' => $this->result, 'mapfile'=>$this->filePath[0]]);
        
       // dd($result);

    }



    
    public $error;
//顯示地圖以及原始資料電子檔
    public function showmap(){

        $fileqx=str_pad($this->qx, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy, 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;
        $filecensus='FDPfiles/fs_census5_scanfile';
        $filemap='';

        if ($this->subqx==1 && $this->subqy==1){
            $filemap='01';
        } else if ($this->subqx==1 && $this->subqy==2){
            $filemap='02';
        } else if ($this->subqx==2 && $this->subqy==2){
            $filemap='03';
        } else {
            $filemap='04';
        }
        $filePath=[];
        $filePath[0]=$filecensus."/".$fileqx.'/map/'.$filesqx.$filemap.'.jpg';
        $filePath[1]=$filecensus."/".$fileqx.'/orimap/'.$filesqx.$filemap.'.jpg';
        $filePath[2]=$filecensus."/".$fileqx.'/old/'.$filesqx.'_old.pdf';
        $filePath[3]=$filecensus."/".$fileqx.'/new/'.$filesqx.'_new.pdf';
//確定是否有檔案
        $error=[];

        for($i=0;$i<4;$i++){
            $matchingFiles = glob(public_path($filePath[$i]) . '.', GLOB_BRACE | GLOB_NOCHECK);

            if (!empty($matchingFiles)) {

                $path[$i] = $filePath[$i];

            } else {
                $error[$i] = '沒有檔案 ' . $filePath[$i];
                $path[$i] = '';
            }
        }
        $this->error=$error;
        $this->filePath=$path;


    }



    protected $listeners = ['updateCoordinates' => 'updateCoordinates'];


//儲存點圖結果
    public function updateCoordinates(Request $request, $data)
    {

       // dd($this->result);

        $qudx = $data['x'];
        $qudy = $data['y'];
        $tag = $data['tag'];
        $rtype = $data['rtype'];


        if ($data['tag']==''){
            $datasavenote='未輸入編號。';
        } else {
// dd($rtype);


            $stemdata = array_filter($this->result, function ($item) use ($tag) {
                return $item['tag'] == $tag;
            });
            $stemdata = reset($stemdata);
            // dd($stemdata);
            if (empty($stemdata)){
                $datasavenote='本區查無此樹('.$tag.')資料。';

            } else {
                $plotx=$stemdata['qx']*20+($stemdata['subqx']-1)*10+$qudx;
                $ploty=$stemdata['qy']*20+($stemdata['subqy']-1)*10+$qudy;

                $sqx=intval(ceil(($qudx+($stemdata['subqx']-1)*10)/5));
                $sqy=intval(ceil(($qudy+($stemdata['subqy']-1)*10)/5));

                if ($qudx=='0' && $qudy!='0'){
                    $sqy=1;
                } 

                if ($qudy=='0' && $qudx!='0'){
                    $sqx=1;
                } 
                // dd($stemdata['sqy']);
                if ($sqx!=$stemdata['sqx'] || $sqy!=$stemdata['sqy']){
                    $datasavenote='('.$tag.')小區不符，請確認。';
                } else {

                    $uplist=['qudx' =>$qudx, 'qudy' => $qudy, 'plotx'=>$plotx, 'ploty'=>$ploty, 'updated_id' =>$this->user];

                    $fixlog['type']='update';
                    $fixlog['id']='0';
                    $fixlog['from']='map';
                    
                    $fixlog['qx']=$stemdata['qx'];
                    $fixlog['stemid']=$tag;
                    $fixlog['descript']=json_encode($uplist, JSON_UNESCAPED_UNICODE);
                    $fixlog['updated_id']=$this->user;
                    $fixlog['updated_at']=date("Y-m-d H:i:s");

                    if ($rtype=='R'){
                        FsTreeBaseR::where('stemid', 'like', $tag)->update($uplist);
                        $fixlog['sheet']='base_r';
                    } else {
                        FsTreeBase::where('tag', 'like', $tag)->update($uplist);
                        $fixlog['sheet']='base';
                    }
                    if($stemdata['qudx']!='0' && $stemdata['qudy']!='0'){
                        FsTreeFixlog::insert($fixlog);
                    }
                    $datasavenote='已更新資料';
                }

            }
        }
        $this->datasavenote=$datasavenote;
        $this->searchSite($request, $this->qx, $this->qy, $this->subqx, $this->subqy);

        // dd($uplist);
        // dd($data);

    }


    protected $listeners2 = ['updateSavenote' => 'updateSavenote'];

    public function updateSavenote(){
        $this->datasavenote='';
    }

//找位置與小區不合的樹
    public $errorList=[];
    public function searchError(){

        $data = FsTreeBase::select('base.*', 'census5.status', 'base.updated_at as update_date')
            ->join('census5', 'census5.tag', '=', 'base.tag')
            ->where('census5.date', '!=', '0000-00-00')
            ->where('census5.branch', '=', '0')
            ->where('census5.status','!=','0')
            ->where('base.deleted_at', '')
            ->where(function ($query) {
                // 使用括號組合 whereRaw 條件，確保任意一條 whereRaw 被滿足
            $query->whereRaw('CAST(IF(base.plotx % 20 = 0,(base.plotx / 20) - 1, FLOOR(base.plotx / 20) ) AS SIGNED) != base.qx')
                  ->orWhereRaw('CAST(IF(base.ploty % 20 = 0,(base.ploty / 20) - 1, FLOOR(base.ploty / 20) ) AS SIGNED) != base.qy')
                  ->orWhereRaw('CAST(IF((base.plotx - (base.qx * 20)) % 10 = 0, (base.plotx - (base.qx * 20)) / 10, CEIL((base.plotx - (base.qx * 20)) / 10)) AS SIGNED) != base.subqx')
                  ->orWhereRaw('CAST(IF((base.ploty - (base.qy * 20)) % 10 = 0, (base.ploty - (base.qy * 20)) / 10, CEIL((base.ploty - (base.qy * 20)) / 10)) AS SIGNED) != base.subqy')
                  ->orWhereRaw('CAST(IF((base.plotx - (base.qx * 20)) % 5 = 0, (base.plotx - (base.qx * 20)) / 5, CEIL((base.plotx - (base.qx * 20)) / 5)) AS SIGNED) != base.sqx' )
                  ->orWhereRaw('CAST(IF((base.ploty - (base.qy * 20)) % 5 = 0, (base.ploty - (base.qy * 20)) / 5, CEIL((base.ploty - (base.qy * 20)) / 5)) AS SIGNED) != base.sqy');

            })

            // 確保 qudx 和 qudy 都不為 0
            ->where('base.qudx', '!=', '0')
            ->where('base.qudy', '!=', '0')
            // ->where('base.tag','236630')
            ->orderBy('base.tag')
            ->get()
            ->toArray();

        $datar = FsTreeBaseR::select('base_r.*', 'census5.status', 'base_r.updated_at as update_date', 'base_r.stemid as tag')
            ->join('census5', 'census5.tag', '=', 'base_r.tag')
            ->where('census5.date', '!=', '0000-00-00')
            ->where('census5.branch', '=', '0')
            ->where('census5.status','!=','0')
            ->where('base_r.deleted_at', '')
            ->where(function ($query) {
                // 使用括號組合 whereRaw 條件，確保任意一條 whereRaw 被滿足
            $query->whereRaw('CAST(IF(base_r.plotx % 20 = 0,(base_r.plotx / 20) - 1, FLOOR(base_r.plotx / 20) ) AS SIGNED) != base_r.qx')
                  ->orWhereRaw('CAST(IF(base_r.ploty % 20 = 0,(base_r.ploty / 20) - 1, FLOOR(base_r.ploty / 20) ) AS SIGNED) != base_r.qy')
                  ->orWhereRaw('CAST(IF((base_r.plotx - (base_r.qx * 20)) % 10 = 0, (base_r.plotx - (base_r.qx * 20)) / 10, CEIL((base_r.plotx - (base_r.qx * 20)) / 10)) AS SIGNED) != base_r.subqx')
                  ->orWhereRaw('CAST(IF((base_r.ploty - (base_r.qy * 20)) % 10 = 0, (base_r.ploty - (base_r.qy * 20)) / 10, CEIL((base_r.ploty - (base_r.qy * 20)) / 10)) AS SIGNED) != base_r.subqy')
                  ->orWhereRaw('CAST(IF((base_r.plotx - (base_r.qx * 20)) % 5 = 0, (base_r.plotx - (base_r.qx * 20)) / 5, CEIL((base_r.plotx - (base_r.qx * 20)) / 5)) AS SIGNED) != base_r.sqx' )
                  ->orWhereRaw('CAST(IF((base_r.ploty - (base_r.qy * 20)) % 5 = 0, (base_r.ploty - (base_r.qy * 20)) / 5, CEIL((base_r.ploty - (base_r.qy * 20)) / 5)) AS SIGNED) != base_r.sqy');

            })
            // 確保 qudx 和 qudy 都不為 0
            ->where('base_r.qudx', '!=', '0')
            ->where('base_r.qudy', '!=', '0')
            // ->where('base_r.tag','236630')
            ->orderBy('base_r.tag')
            ->get()
            ->toArray();

            $data = is_array($data) ? $data : [];
            $datar = is_array($datar) ? $datar : [];

            // 合并两个数组
            $datacom = array_merge($data, $datar);

            // dd($datacom);

            if ($datacom==[]){
                $this->errorList='樹位置與小區皆相符';
            } else {
                $this->errorList=$datacom;
            }

        
    }


    public function render()
    {
        return view('livewire.fushan.tree-map');
    }
}
