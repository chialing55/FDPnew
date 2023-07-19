<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class updateController extends Controller
{
    public function latestUpdates()
    {
        $latestUpdate = null;

        $folders = [
            public_path('css'),
            app_path('Http'),
            resource_path('views'),
        ];


        foreach ($folders as $folder) {
            $folderUpdate = $this->getFolderLatestUpdate($folder);
            
            if ($folderUpdate > $latestUpdate) {
                $latestUpdate = $folderUpdate;
            }
        }
        $date = date('Y-m-d', $latestUpdate);

        return $date;
    }

    private function getFolderLatestUpdate($folder)
    {
        $latestUpdate = null;

        $files = File::allFiles($folder);

        foreach ($files as $file) {
            $fileUpdate = $file->getMTime();

            if ($fileUpdate > $latestUpdate) {
                $latestUpdate = $fileUpdate;
            }
        }

        return $latestUpdate;
    }
}
