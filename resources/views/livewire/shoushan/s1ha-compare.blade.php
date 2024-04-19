<div>
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

            <table class='finishtable'border="1" cellpadding="1" cellspacing="0" style='margin-bottom: 20px;'>

                @for ($i=19;$i>12;$i--)
                <tr>
                    <td style='width:25px'>{{$i}}</td>
                    @for($j=-4;$j<11;$j++)
                    @php 
                    $finishSiteClass='';
                    $plot=$j.'-'.$i;
                    // echo $plot."<br>";
                    if (!in_array($plot, $entrycom1) && !in_array($plot, $entrycom2)) {
                    $finishSiteClass = 'entryallfin ';
                    } elseif (!in_array($plot, $entrycom1)) {
                    $finishSiteClass = 'entry1fin ';
                    } elseif (!in_array($plot, $entrycom2)) {
                    $finishSiteClass = 'entry2fin ';
                    }
                    @endphp

                    <td class='{{$j}}-{{$i}}  {{$finishSiteClass}}'></td>

                    @endfor
                </tr>
                @endfor
                                <tr>
                    <td></td>
                    @for($i=-4;$i<11;$i++)
                    <td style='width:25px'>{{$i}}</td>
                    @endfor
                </tr>
            </table>

        </li>
        <li><b>完成輸入可進行資料檢查</b>，以確認輸入正確。

             <div style='display: flex; margin: 10px 0;'>
                <div>
                    @if($entry1done=='1')
                        <span>第一次輸入完成</span>
                    @else
                    <button wire:click.prevent="entryFinish(1)" wire:loading.attr="disabled">第一次輸入檢查</button>
                    @endif
                </div>
                <div>
                    @if($entry2done=='1')
                        <span style="margin-left: 20px;">第二次輸入完成</span>
                    @else
                    <button wire:click.prevent="entryFinish(2)" wire:loading.attr="disabled" style="margin-left: 20px;" >第二次輸入檢查</button>
                    @endif
                </div>
            </div>
            <div style="margin-bottom: 10px;">
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
        @if($entry2done=='1' && $entry1done=='1')
        <button wire:click.prevent="compare()" wire:loading.attr="disabled">資料比對</button>
        <div style="margin-bottom: 10px;">
            <span wire:loading wire:target="compare">
                     檢查中....
            </span>
        </div>
        @if($comnote!='')
        <div style='margin-top:20px;'>
            {!!$comnote!!}
        </div>
        @endif
        @endif
    </div>

    <div class="text_box"><h2>建立大表</h2>
        <hr>
        <p>資料比對完成後即可建立大表。並不再開放資料輸入，如需更新資料，轉往資料修改頁面。</p>
        @if($comparedone=='1')
        <button wire:click.prevent="createTable()" wire:loading.attr="disabled">建立大表</button>
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
