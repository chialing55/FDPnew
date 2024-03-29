<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use App\Models\FsTreeEntrycom;


class FsTreeCensus5Progress
{
	public function showProgress(){

        $comparelist=[];
        $updatelist=[];
        $directorieslist=[];

        $compareok=FsTreeEntrycom::select('qx',  'compareOK')->where('compareOK', '!=', '0')->groupBy('qx', 'compareOK')->get()->toArray();
        $updateok=FsTreeEntrycom::select('qx',  'census5update')->where('census5update', '!=', '0')->groupBy('qx', 'census5update')->get()->toArray();
        $alternoteOK=FsTreeEntrycom::select('qx',  'alternoteOK')->where('alternoteOK', '!=', '')->groupBy('qx', 'alternoteOK')->get()->toArray();


        foreach ($compareok as $entry){
                $comparelist[]=$entry['qx'];
        }

        foreach ($updateok as $entry){
                $updatelist[]=$entry['qx'];
        }

        foreach ($updateok as $entry){
                $alternotelist[]=$entry['qx'];
        }        
        // dd($compareok);

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


