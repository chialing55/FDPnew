<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\FsBaseTreeSplist;
use App\Models\FsTreeCensus4;
use App\Models\FsTreeCensus3;

class fsTreeAddButton
{
	public function addbutton($records, $entry){


        for($q=0;$q<count($records);$q++){
            $stemid = $records[$q]['stemid'];
            $entry = $entry;

            if ($q==0){ $thispage=1;} else { $thispage=ceil($q/20);}


            // HTML 輸出編碼
            $escapedStemid = htmlspecialchars($stemid);
            $escapedEntry = htmlspecialchars($entry);
            $escapedThispage = htmlspecialchars($thispage);

            // 生成 HTML 按鈕元素
            $button1 = "<button name='deletedata' deleteid='$escapedStemid' onclick='deleteid(\"$escapedStemid\", \"$escapedEntry\", \"$escapedThispage\")'><i class='fa-solid fa-xmark'></i></button>";
            $button2 = "<button name='alternoteshow{$escapedStemid}'  onclick='alternote(\"$escapedStemid\", \"$escapedEntry\", \"$escapedThispage\")'><i class='fa-regular fa-note-sticky'></i></button> ";
            $records[$q]['alternotetable']=$records[$q]['alternote'];


            if ($records[$q]['status']=='-9'){   //status==-9，不會有alternote
                $records[$q]['alternotetable'] = $button1;
            } else {


                    $records[$q]['alternotetable']=$button2.$records[$q]['alternotetable'];

                
            }           
        }

		return $records;

	}
}


