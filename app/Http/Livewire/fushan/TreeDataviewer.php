<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;



class TreeDataviewer extends Component
{

    public $qx='0';
    public $qy='0';
    public $census='1';
    public $oldnew='old';
    public $error='';
    public $path='';
    public $filePath2='';
    public $map='1';

    public function mount(Request $request)
    {
        $this->processFile($request);

    }

    public function change(Request $request)
    {
        $this->processFile($request);
    }


    public function processFile(Request $request)
    {

        $user = $request->session()->get('user', function () {
            return 'no';
        });

        $fileqx=str_pad($this->qx, 2, '0', STR_PAD_LEFT);
        $fileqy=str_pad($this->qy, 2, '0', STR_PAD_LEFT);
        $filesqx=$fileqx.$fileqy;
        $filecensus='fs_census'.$this->census."_scanfile";


///fs_census4_scanfile/'.$fileqx.'/old/'.$filesqx.'_old.pdf
        $filePath=$filecensus."/".$fileqx.'/'.$this->oldnew.'/'.$filesqx.'_'.$this->oldnew.'';
        // $filePath2=public_path($filePath1);
        if ($this->oldnew=='map'){
            $filemap=str_pad($this->map, 2, '0', STR_PAD_LEFT);
            $filePath=$filecensus."/".$fileqx.'/'.$this->oldnew.'/'.$filesqx.$filemap.'';
        }

        $matchingFiles = glob(public_path($filePath) . '.*', GLOB_BRACE | GLOB_NOCHECK);

        $this->filePath2=$matchingFiles;


        if (!empty($matchingFiles)) {

            foreach ($matchingFiles as $matchingFile) {
                $info = pathinfo($matchingFile);
                
                if ($info['extension'] === 'pdf') {
                    // 這是 PDF 檔案
                    $this->path = $filePath.".pdf";

                    $this->error = '';
                    break;
                } elseif ($info['extension'] === 'PDF'){
                    $this->path = $filePath.".PDF";
                    $this->error = '';
                    break;
                } elseif ($info['extension'] === 'jpg'){
                    $this->path = $filePath.".jpg";
                    $this->error = '';
                    break;
                }else {
                    $this->error = '沒有檔案 ' . $filePath;
                    $this->path='';
                }
            }
        } else {
            $this->error = '沒有檔案 ' . $filePath;
            $this->path='';
        }



        // if (file_exists($matchingFiles)) {
        //     // return Redirect::away($filePath1);
        //     $this->path=$filePath1;
        //     $this->error='';
        // } else {
        //     $this->error='沒有檔案 '.$filePath1;
        //     $this->path='';
        // }

    }

    public function render()
    {
        return view('livewire.fushan.tree-dataviewer');
    }
}
