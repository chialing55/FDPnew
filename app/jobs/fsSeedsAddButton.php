<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsSeedsRecord1;
use App\Models\FsSeedsSplist;

class fsSeedsAddButton
{
	public function addbutton($entrytable){


        for($i=0;$i<count($entrytable);$i++){
            if ($i==0){ $thispage=1;} else { $thispage=ceil($i/20);}

            // HTML 輸出編碼
            $escapedTag = htmlspecialchars($entrytable[$i]['id']);
            $escapedInfo= htmlspecialchars("trap".$entrytable[$i]['trap']."-".$entrytable[$i]['csp']."-code".$entrytable[$i]['code']);
            $escapedThispage = htmlspecialchars($thispage);


            $button1 = "<button name='deletedata' onclick='deleteid(\"$escapedTag\",\"$escapedInfo\", \"$escapedThispage\")')><i class='fa-solid fa-xmark'></button>";
            $entrytable[$i]['d']=$button1;
        }

		return $entrytable;

	}






}


