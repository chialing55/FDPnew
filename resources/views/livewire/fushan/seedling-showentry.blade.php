<div>
    <h2>{{$year}} 年 {{$month}} 月 第 {{$census}} 次調查 - 第 {{$entry}} 次資料輸入</h2>
    <div style='margin-top:10px'>
        <p>請先詳閱小苗輸入注意事項</p> 
        <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
    </div>
 

    <div>
        <p style='font-weight: 800;'>
            選擇要輸入的樣站 
            
            <select name="trap" class="fs100"  wire:model='selectTrap' wire:change="searchtrap($event.target.value)">
            
            @for ($i=1; $i<108;$i++)
              @if($i==42)continue;
              @else
            <option value="{{$i}}">{{$i}} 
                
            </option>
            @endif
             @endfor
        
            </select>

            
            


        @if (!$record)
        <button class="ui button" wire:click="searchtrap({{$selectTrap}})">enter</button>
        <span style='padding-left:20px'>*{{$entrynote}}</span>
        @else
            @if($selectTrap>1)
            <span style='padding-left:20px'><a class='a_' wire:click="searchtrap({{($selectTrap-1)}})">上一個</a></span>
            @endif
            @if($selectTrap<107)
              <span style='padding-left:20px'><a class='a_' wire:click="searchtrap({{($selectTrap+1)}})">下一個</a></span>
            @endif  
        @endif

        </p>
        
    </div> 
<div>

    @if($record)



<div class='text_box'>
   
<h6>樣站環境資料</h6>
<hr>

    <div class='simplenote text_box'>
        <ul>
        <li>樣區上方光度 - <b>U</b>: 多層樹冠 (預設)，<b>I</b>: 一層樹冠，<b>G</b>: 沒有樹冠。</li>
        </ul>
    </div>

    <div class='entrytablediv'>
        {{-- <h2>測試</h2> --}}
        <span class='covsavenote savenote'>
            
       </span>
        <div id='covtable{{$covs[0]['trap']}}' class='test fs100' ></div>
        <p style='margin-top:5px; text-align: right'><button name='covsave{{$covs[0]['trap']}}' >儲存</button></p>
    </div>
</div>

<div class='text_box'>

<h6>小苗調查資料</h6>
<hr>

<div class='simplenote text_box'>
    <ul>

    <li>長度及葉片數 - <b>-1</b>: 沒有測量，<b>-2</b>: DBH >= 1，<b>-4</b>: 見環不見苗或死亡，<b>-6</b>: 消失，<b>-7</b>: 主幹或分枝死亡但個體存活。<span class='line'>長度不可為 0</span>。</li>
    <li>狀態 - <b>A</b>: 存活，<b>G</b>: 見環不見苗，<b>D</b>: 死亡，<b>N</b>: 消失，<b>L</b>: 離開。</li>
    <li>特殊修改 - 需要更正 Trap, Plot, Tag, 種類, 原長度和原葉片數，請點選<i class='fa-regular fa-note-sticky'></i>填寫。</li>
    </ul>
</div>
        @if($record=='無')
           <div style='margin-top:20px'><p> 沒有舊資料</p></div>
        
        @else
    <div class='entrytablediv'>
        {{-- <h2>測試</h2> --}}
        <span class='seedlingsavenote savenote'>
            
       </span>
       <div class='pages'>
           <div class='pagenote'></div>
           <div class='prev'>上一頁</div>
           <div class='next'>下一頁</div>
       </div>
        <div id='seedlingtable{{$covs[0]['trap']}}'></div>
        <p style='margin-top:5px; text-align: right;'><button name='seedlingsave{{$covs[0]['trap']}}' class="save2">儲存</button></p>

        <div class='alternotetalbeouter'>
            <h6 class='alterh6'>特殊修改</h6>
            <p ><span class='altertag'></span>
            <span class='altersavenote savenote'></span></p>
            <div id='alternotetable' style='margin-top: 5px;' class='fs100' ></div>


            <p style='margin-top:10px; text-align: right;'><button name='alternotesave' >儲存</button>

            <button name='deletealternote' class='deletealternotebutton' onclick="deletealternoteButtonClick(this)">刪除此資料</button>
            <button class='close' onclick="$('.alternotetalbeouter').hide(); $('.alternotetable').html();" >X</button>

            </p>
        </div>

    </div>
</div>            
{{-- @include('livewire.'.$site.'.seedling-seedling-entry') --}}

        @endif   

<div> <button class='recruit' onclick="$('.recruittableout').toggle();">新增苗</button> 
    <button name='sroll' class="" onclick="$('.slrolltableout').toggle();">撿到環</button>
</div>    

<div class='recruittableout text_box' style='margin-top: 20px;'>
   <h6>新增苗</h6>
   <hr>
   <div class='simplenote text_box'>
    <ul>

        <li>長度及葉片數 - <b>-1</b>: 沒有測量，<b>-2</b>: DBH >= 1。<span class='line'>長度不可為 0</span>。</li>
        <li>新增狀態 - <b>R</b>: 新增 (預設)，<b>O</b>: 舊苗，<b>T</b>: DBH > = 1 ，因有萌蘗而被記錄的樹。</li>
        <li>萌蘗狀態 - <b>TRUE</b>: 萌蘗苗，<b>FALSE</b>: 種子苗 (預設)。</li>
        <li>萌蘗苗不需有位置資料。</li>
        <li>資料不完整即不予處理。</li>
    </ul>
    </div>
    <div class='entrytablediv'>
        <p class='recruitsavenote savenote'></p>
        
        <p style='text-align: right'><button name='recruitsave{{$covs[0]['trap']}}' class="save2">儲存</button></p>
        <div id='recruittable{{$covs[0]['trap']}}' style='margin-top: 10px;'></div>
        <p style='margin-top:5px;'><button name='clearrecruittable' class="save2">清空新增表單</button></p>
        </div>
    </div>
    
<div class='slrolltableout text_box' style='margin-top: 20px;'>

    <h6>撿到環</h6>
    <hr>
    <div class='entrytablediv'>
        <p class='slrollsavenote savenote'></p>
        
        <p style='text-align: right'><button name='slrollsave{{$covs[0]['trap']}}' class="save2">儲存</button></p>
        <div id='slrolltable{{$covs[0]['trap']}}' style='margin-top: 10px;'></div>

        </div>
    </div>

</div>

    @endif
 </div>




    

{{-- <table id="grid_id"><tbody></tbody></table> --}}

</div>
