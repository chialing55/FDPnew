<div>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
    <h2>資料比對</h2>

    <div>
        <div style='margin:20px 0 20px 0; display:flex;'>
            <div style='display:inline-flex; margin-right:30px;'>已完成兩次輸入 <div class='entryallfin entryfinshow' ></div></div>
            <div style='display:inline-flex; margin-right:30px;'>比對完成 <div class='comparefin  entryfinshow'></div></div>
        </div>
        <table class='finishtable'border="1" cellpadding="1" cellspacing="0" style=''>

        <tr>
        @for ($i=0;$i<25;$i++)  
            @php 
                if (in_array($i, $entrylist)){
                    $finishSiteClass='entryallfin';
                } else {
                    $finishSiteClass='';
                }

                if (in_array($i, $comparelist)){
                    $finishSiteClass='comparefin ';
                } else {
                    // $finishSiteClass='';
                }

            @endphp
            <td style='width:25px' class=' {{$finishSiteClass}}'>{{$i}}</td>
        @endfor
        </tr>

        </table>
    </div>
    <div id='simplenote' class='text_box'>
         {{-- <h2>資料比對注意事項</h2> --}}
         <ul>
            <li><b>已完成兩次輸入的樣線即可進行資料比對。</b></li>
            <li><b>請由第三方進行比對，並修改錯誤資料。</b></li>
            <li>更新資料後記得按<button class='datasavebutton' style='width: auto;'>輸入完成</button>，才能再進行比對。</li>
{{--              <li></li>
             <li></li>
             <li></li>
             <li></li> --}}
         </ul>
        
    </div>
    <div class='text_box'>    
        <h2>選擇要進行資料比對的樣線</h2>
        <div style='margin-top: 20px;'>
        <form wire:submit.prevent='compare'>
            <select name="qx" class="fs100 entryqx" wire:model='qx' style='height:25px;'>
                <option value=""></option>
            @for ($i=0; $i<25;$i++)
            @php 
                if (in_array($i, $entrylist) && !in_array($i, $comparelist)){
                    echo "<option value=".$i.">".$i."</option>";
                } 
            @endphp
            {{-- <option value="{{$i}}">{{$i}}</option> --}}
             @endfor
            </select>
            <button type="submit" style='margin-left: 20px;'>開始比對</button>
        </form>
    </div>

    </div>

    @if (isset($comnote))
    <div class='text_box' style='background-color:lightyellow;'> 
        <h2>比對結果</h2>
        <p style="margin: 10px 0">

        {!!$comnote!!}</p>
       
        {{-- <a href='/fstree-compare-pdf' target='_blank'><button>將比對結果輸出成 pdf 檔</button></a> --}}
        
           
    </div>
    @endif
</div>
