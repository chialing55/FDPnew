<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use App\Models\FsTreeComplete;

//Census5資料整理進度
class FsTreeCensus5Progress
{
	public function showProgress(){

        $comparelist=[];
        $updatelist=[];
        $directorieslist=[];
        $alternotelist=[];

        $compareDone=FsTreeComplete::select('qx',  'compareDone')->where('compareDone', '!=', '')->groupBy('qx', 'compareDone')->get()->toArray();
        $updateDone=FsTreeComplete::select('qx',  'addToMainTable')->where('addToMainTable', '!=', '')->groupBy('qx', 'addToMainTable')->get()->toArray();
        $alternoteDone=FsTreeComplete::select('qx',  'alternoteDone')->where('alternoteDone', '!=', '')->groupBy('qx', 'alternoteDone')->get()->toArray();


        foreach ($compareDone as $entry){
                $comparelist[]=$entry['qx'];
        }

        foreach ($updateDone as $entry){
                $updatelist[]=$entry['qx'];
        }
//上傳完成輸進大表就可以點圖
        foreach ($updateDone as $entry){
                $alternotelist[]=$entry['qx'];
        }        
        // dd($compareDone);

        $filecensus='fs_census5_scanfile';
        $directoryPath = public_path($filecensus);
        // Get all subdirectories in the specified path
        $directories = File::directories($directoryPath);

        // Extract only the directory names
        $directorieslist = array_map('basename', $directories);
        // dd($this->directories);

	return $result=[
            'updatelist'=> $updatelist,
            'comparelist'=>$comparelist,
            'directorieslist'=>$directorieslist,
            'alternotelist'=>$alternotelist
        ];

	}

}


