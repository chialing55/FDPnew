
        <div style='margin:20px 0 20px 0;'>
            <div style='display:inline-flex; margin-right:30px;'>比對完成 <div class='comparefin  entryfinshow'></div></div>

            <div style='display:inline-flex; margin-right:30px;'>已上傳檔案 <div class='loadingfin entryfinshow' ></div></div>
            <div style='display:inline-flex; margin-right:30px;'>已匯入大表 <div class='importfin entryfinshow' ></div></div>            
        </div>


        <table class='finishtable'border="1" cellpadding="1" cellspacing="0" style=''>
        <tr>
        @for ($i=0;$i<25;$i++)  
            @php 
                $finishSiteClass='';
                if (in_array($i, $comparelist)){
                    $finishSiteClass='comparefin ';
                } 

                if (in_array($i, $directories)){
                    $finishSiteClass='loadingfin ';
                } 

                if (in_array($i, $updatelist)){
                    $finishSiteClass='importfin ';
                }

            @endphp
            <td style='width:25px' class=' {{$finishSiteClass}}'>{{$i}}</td>
        @endfor
        </tr>

        </table> 