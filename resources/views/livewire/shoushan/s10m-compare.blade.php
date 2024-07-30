<div>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
    <div class="text_box">
        <h2>輸入進度</h2>
        <hr>
        <ul>
        <li><b>是否已完成輸入? </b>(每一筆資料皆有日期)
            <div style='margin:10px 0 10px 0; display:flex;'>
                <div style='display:inline-flex; margin-right:30px;'>第一次輸入 <div class='entry1fin entryfinshow' ></div></div>
                <div style='display:inline-flex; margin-right:30px;'>第二次輸入 <div class='entry2fin entryfinshow'></div></div>
                <div style='display:inline-flex; margin-right:30px;'>兩次輸入 <div class='entryallfin entryfinshow'></div></div>

            </div>

        <table class='finishtable' border="0" cellpadding="0" cellspacing="0" style='margin-bottom: 20px;'>

            @for($i=0;$i<count($plots1Array);$i++)

            <tr>
                @foreach ($plots1[$plots1Array[$i]] as $plot)
                @php 
                    $finishSiteClass='';
                    if (!in_array($plot, $entrydone1) && !in_array($plot, $entrydone2)) {
                    $finishSiteClass = 'entryallfin ';
                    } elseif (!in_array($plot, $entrydone1)) {
                    $finishSiteClass = 'entry1fin ';
                    } elseif (!in_array($plot, $entrydone2)) {
                    $finishSiteClass = 'entry2fin ';
                    }

                @endphp
                    <td class='table_td_border {{$finishSiteClass}}' >{{ $plot }}</td>
                @endforeach
            </tr>
            @endfor

        </table>

        </li>
        <li><b>完成輸入可進行資料檢查</b>，以確認輸入正確。

             <div style='display: flex; margin: 10px 0;'>
                <div>
                    @if($entry1done=='1')
                        <span>第一次輸入完成</span>
                    @else
                    <button wire:click.prevent="entryFinish(1)">第一次輸入檢查</button>
                    @endif
                </div>
                <div>
                    @if($entry2done=='1')
                        <span style="margin-left: 20px;">第二次輸入完成</span>
                    @else
                    <button wire:click.prevent="entryFinish(2)" style="margin-left: 20px;" >第二次輸入檢查</button>
                    @endif
                </div>
            </div>

        <div style="margin: 10px 0;">
            <span wire:loading wire:target="entryFinish">
                     檢查中....
            </span>
        </div>

            @if($finishEntry!='')
            <div>
                <h6>{{$finishEntry}} 檢查結果</h6>
                <hr>
                {!!$finishnote!!}
            </div>
            @endif
        </li>

        </ul>
    </div>
    <div class="text_box">
        <h2>資料比對</h2>
        <hr>
        <p>兩次輸入皆通過檢查後，即可進行資料比對。</p>
        
        <button wire:click.prevent="compare()">資料比對</button>

        <div style="margin: 10px 0;">
            <span wire:loading wire:target="compare">
                     檢查中....
            </span>
        </div>

            @if($comnote!='')
            <div style='margin-top:20px;'>
                {!!$comnote!!}
            </div>
            @endif
       
    </div>
    <div class="text_box"><h2>建立大表</h2>
        <hr>
        <p>資料比對完成後即可建立大表。並不再開放資料輸入，如需更新資料，轉往資料修改頁面。</p>
        @if($comparedone=='1')
        <button wire:click.prevent="createTable()">建立大表</button>
        <div style="margin-top: 10px;">
            <span wire:loading wire:target="createTable">
                     建立中....
            </span>
        </div>
        @endif
        @if($createTablenote!='')
        <div style='margin-top:20px;'>
            {!!$createTablenote!!}
        </div>
        @endif
    </div>
    {{-- Care about people's approval and you will be their prisoner. --}}
</div>
