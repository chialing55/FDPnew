<?php 

namespace App\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

use App\Models\FsSeedlingData;
use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;

class fsSeedlingAddButton
{
	public function addbutton($records, $entry){

        if ($entry == '1') {
            $table= new FsSeedlingSlrecord1;
        } else {
            $table= new FsSeedlingSlrecord2;
        }


        for($q=0;$q<count($records);$q++){
            $tag = $records[$q]['tag'];
            $entry = $entry;

            if ($q==0){ $thispage=1;} else { $thispage=ceil($q/20);}


            // HTML 輸出編碼
            $escapedTag = htmlspecialchars($tag);
            $escapedEntry = htmlspecialchars($entry);
            $escapedThispage = htmlspecialchars($thispage);
            $maxid=FsSeedlingSlrecord::count();
            $records[$q]['alternotetable']=$records[$q]['alternote'];

            // 生成 HTML 按鈕元素
            $button1 = "<button name='deletedata' onclick='deleteid(\"$escapedTag\", \"$escapedEntry\", \"$escapedThispage\")')><i class='fa-solid fa-xmark'></button>";
            $button2 = "<button name='alternoteshow{$escapedTag} '  onclick='alternote(\"$escapedTag\", \"$escapedEntry\", \"$escapedThispage\", event)' class='alternotehover'><i class='fa-regular fa-note-sticky'></i></button> ";


           
            if ($records[$q]['id']>$maxid && $records[$q]['alternotetable']==''){   //id>maxid，不會有alternote
                $records[$q]['alternotetable'] = $button1;
            } else {

                    $records[$q]['alternotetable']=$button2.$records[$q]['alternotetable'];
                    // $records[$q]['alternote']=$records[$q]['alternote']."<input type='submit' value='特殊修改' name='deletedata' deleteid='".$records[$q]['stemid']."' wire:click='alternote(".$records[$q]['stemid'].")'></input>";
            }         
        }

		return $records;

	}






}


