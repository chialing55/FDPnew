<div class='flex text_outbox' style='flex-direction: column;' >
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
    <div class='text_box'>
        <h2>每日輸入進度</h2>
        <hr>
        <div id='fstreeprogresstable'>
            <table id='progressTable' class='tablesorter' style='text-align: center'>
                <thead>
                    <tr >
                        <th style='text-align: center;'>輸入日期</th>
                        <th style='width:80px; text-align: center;'>輸入者</th>
                        <th style='width:80px; text-align: center;'>輸入筆數</th>
                        
                    </tr>
                </thead>
                @if(!empty($entrytable))
                <tbody>
                    @foreach($entrytable as $table)
                        @foreach($table as $pro)
                    <tr>
                        <td>{{$pro['date1']}}</td>
                        <td>{{$pro['name']}}</td>
                        <td>{{$pro['pps']}}</td>
                        
                    </tr>
                    @endforeach
                   @endforeach
                </tbody>
                @endif
            </table>
        </div>
    </div>


    <div class='text_box'>
        <h2>輸入完成樣區</h2>
        <hr>
        <div style='margin:10px 0 0 0px'>
            <div style='margin:0 0 20px 0; display:flex;'>
                <div style='display:inline-flex; margin-right:30px;'>第一次輸入完成 <div class='entry1fin entryfinshow' ></div><div> ({{($countFinishSite1/625)*100}} %) </div></div>
                <div style='display:inline-flex; margin-right:30px;'>第二次輸入完成 <div class='entry2fin entryfinshow'></div><div> ({{($countFinishSite2/625)*100}} %) </div></div>
                <div style='display:inline-flex; margin-right:30px;'>兩次輸入完成 <div class='entryallfin entryfinshow'></div><div> ({{($countFinishSiteall/625)*100}} %) </div></div>
            </div>
            <table class='finishtable'border="1" cellpadding="1" cellspacing="0" style=''>

                @for ($i=24;$i>-1;$i--)
                <tr>
                    <td style='width:25px'>{{$i}}</td>
                    @for($j=0;$j<25;$j++)
                    @php 
                    if($finishSite["'".$j."-".$i."'"]=='10'){
                        $finishSiteClass='entry1fin';
                    } else if ($finishSite["'".$j."-".$i."'"]=='01'){
                        $finishSiteClass='entry2fin';
                    } else if ($finishSite["'".$j."-".$i."'"]=='11'){
                        $finishSiteClass='entryallfin';
                    } else {
                        $finishSiteClass='';
                    }
                    @endphp

                    <td class='{{$j}}-{{$i}}  {{$finishSiteClass}}'></td>

                    @endfor
                </tr>
                @endfor
                                <tr>
                    <td></td>
                    @for($i=0;$i<25;$i++)
                    <td style='width:25px'>{{$i}}</td>
                    @endfor
                </tr>
            </table>
        </div>
    </div>

</div>
