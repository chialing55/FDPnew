<div>
        <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
    <h2>{{$year}} 年 {{$month}} 月 第 {{$census}} 次調查 - 第 {{$entry}} 次資料輸入</h2>
    <div style='margin-top:10px'>
        <p>請先詳閱<a href="{{asset('/fushan/seedling/note')}}">小苗輸入注意事項</a></p> 
        <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
    </div>
 

    <div>
        <p style='font-weight: 800;'>
            選擇要輸入的樣站 
            
            <select name="trap" class="fs100"  wire:model='selectTrap' wire:change="searchtrap($event.target.value)">
             <option value=""> 
                
            </option>
            @foreach ($trapOptions as $trapOption)
            <option value="{{$trapOption}}">{{$trapOption}}</option>
            @endforeach
        
            </select>


        @if (!$record)
        <span style='padding-left:20px'>*{{$entrynote}}</span>
        @else
            <span style='display:inline-flex; align-items:center; padding-left:20px; gap:20px;'>
                <span style='display:inline-block; min-width:48px;'>
                    @if(!is_null($previousTrap))
                        <a class='a_' wire:click="searchtrap({{$previousTrap}})">上一個</a>
                    @else
                        <span style='visibility:hidden;'>上一個</span>
                    @endif
                </span>
                <span style='display:inline-block; min-width:48px;'>
                    @if(!is_null($nextTrap))
                        <a class='a_' wire:click="searchtrap({{$nextTrap}})">下一個</a>
                    @else
                        <span style='visibility:hidden;'>下一個</span>
                    @endif
                </span>
            </span>
        @endif

        </p>
        
    </div> 
<div>

    @if($record)

<div class='text_box'>
   
<h6>樣站環境資料</h6>
<hr>

    <div id='simplenote' class='text_box2'>
        <ul>
        <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
        <li>樣區上方光度 - <b>U</b>: 多層樹冠 (預設)，<b>I</b>: 一層樹冠，<b>G</b>: 沒有樹冠。</li>
        </ul>
    </div>

    <div class='entrytablediv seedling-cov-panel' data-seedling-cov-site='{{$covs[0]['trap']}}'>
        {{-- <h2>測試</h2> --}}
        <span class='covsavenote savenote app-feedback-note'></span>
        <div id='covtable{{$covs[0]['trap']}}' class='test fs100' ></div>
        <p style='margin-top:5px; text-align: center'><button name='covsave{{$covs[0]['trap']}}' class='datasavebutton' style='width:550px'>儲存</button></p>
    </div>
</div>

<div class='text_box'>

<h6>小苗調查資料</h6>
<hr>

<div id='simplenote' class='text_box2'>
    <ul>
        <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
        <li>長度及葉片數：<b>-1</b>: 沒有測量，<b>-2</b>: DBH >= 1，<b>-4</b>: 見環不見苗或死亡，<b>-6</b>: 消失，<b>-7</b>: 主幹或分枝死亡但個體存活。<span class='line'>長度不可為 0</span>。</li>
        <li>狀態：<b>A</b>: 存活，<b>G</b>: 見環不見苗，<b>D</b>: 死亡，<b>N</b>: 消失。</li>
        <li>note：統一使用「中文」標點符號。「半形」英文符號。「半形」阿拉伯數字，數字後留一格空白。先確認原始 note，加「。」，再輸入本次note。不同類型 note 間用「。」分隔。暫時註記的內容(需特殊修改或撿到環等)，不需填入。</li>
        <li>特殊修改：如需要更正 Trap, Plot, Tag, 種類, 原長度和原葉片數，請點選「特殊修改 <i class='fa-regular fa-note-sticky'></i>」 填寫。</li>
        <li>按「<i class='fa-regular fa-note-sticky'></i>」後，會先儲存資料，若有資料錯誤，請先修改後再填寫特殊修改。</li>
        <li>請參考輸入說明之特殊情況處理。</li>
        <li>需後端特殊處理的資料，請填寫 <a href='https://docs.google.com/spreadsheets/d/14MX9HtYQLqpHa5iQ9PBnJDfC1Qf5n4mio-wzF_k7MBU/edit?usp=sharing' target="_blank">福山小苗調查待後端更正的資料</a></li>
        <li>資料輸入完成至 Trap=107 時，請按<button class='datasavebutton' style='width: auto;'>輸入完成</button>，以做最後檢查。</li>
    </ul>
</div>

@php
// print_r($record[1][1]);

$tableVar=$selectTrap;
$alterOtherNote="";
@endphp

    <div class='entrytablediv seedling-data-panel' data-seedling-data-site='{{$tableVar}}'>
        @if($record[0]['tag']=='無')
           <div class='seedling-empty-note' style='margin:20px 0'><p>沒有舊資料</p></div>
        @endif

        <div class='seedling-data-table-shell' @if($record[0]['tag']=='無') style='display:none;' @endif>
@include('includes.str-main-entrytable')
        </div>
        {{-- <h2>測試</h2> --}}
{{--         <span class='seedlingsavenote savenote'></span>
       <div class='pages'> 
           <div class='pagenote'></div>
           <div class='prev'>上一頁</div>
           <div class='next'>下一頁</div>
       </div>
        <div id='seedlingtable{{$covs[0]['trap']}}'></div>
        <p style='margin-top:5px; text-align: center;'><span class='seedlingsavenote savenote' style='margin: 0 30px 0 0'></span><button name='seedlingsave{{$covs[0]['trap']}}' class=" datasavebutton" >儲存</button></p>

        <div class='alternotetalbeouter'>
            <h6 class='alterh6'>特殊修改<span style='margin-left: 20px; font-size: 80%; font-weight: 500;'>*只需填寫需修改的資料</span></h6>
            <p ><span class='altertag'></span>
            <span class='altersavenote savenote'></span></p>
            <div id='alternotetable' style='margin-top: 5px;' class='fs100' ></div>


            <p style='margin-top:10px; text-align: right;'><button name='alternotesave' class='datasavebutton' style='width: auto;'  >儲存</button>

            <button name='deletealternote' class='deletealternotebutton' onclick="deletealternoteButtonClick(this)">刪除此資料</button>
            <button class='close' onclick="$('.alternotetalbeouter').hide(); $('.alternotetable').html();" >X</button>

            </p>
        </div> --}}

    </div>
</div>            
{{-- @include('livewire.'.$site.'.seedling-seedling-entry') --}}

        

<div style='margin-left: 30px;'> 
    <button class='recruit recruitbutton' onclick="toggleSeedlingRecruitPanel('{{$covs[0]['trap']}}')">新增苗</button> 
    <button name='sroll' class="rollbutton" onclick="toggleSeedlingRollPanel('{{$covs[0]['trap']}}')">撿到環</button>
@php
    $lastTrap = !empty($trapOptions) ? (string) $trapOptions[array_key_last($trapOptions)] : null;
@endphp
@if((string) $selectTrap === $lastTrap)

<div style='margin-top: 20px;'>
<button class='finish finishbutton' onclick="finish({{$entry}})">輸入完成</button>
<span class='finishnote savenote app-feedback-note'></span>


</div>
@endif
</div>    



<div class='recruittableout text_box seedling-recruit-panel' data-seedling-recruit-site='{{$covs[0]['trap']}}' style='margin-top: 20px;'>
   <h6>新增苗</h6>
   <hr>
   <div id='simplenote' class='text_box2'>
    <ul>
        <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
        <li>長度及葉片數：<b>-1</b>: 沒有測量，<b>-2</b>: DBH >= 1。<span class='line'>長度不可為 0</span>。</li>
        <li>新增狀態：<b>R</b>: 新增 (預設)，<b>O</b>: 舊苗，<b>T</b>: DBH > = 1 ，因有萌櫱而被記錄的樹。</li>
        <li>萌櫱狀態：<b>TRUE</b>: 萌櫱苗，<b>FALSE</b>: 種子苗 (預設)。</li>
        <li><span class='line'>萌櫱苗不需有位置資料。</span></li>
        <li>如果為已死小苗的新增萌櫱(資料中無主幹資料)，請通知資料管理員。</li>
        <li><b>若小苗更改植物種類，但有新增萌糵苗，造成種類名稱不同</b>：新增萌糵苗的種類先輸入原有種類，並在主幹的note欄位備注「有新增萌糵苗需改種類名稱」</li>
        <li>資料不完整即不予處理。</li>
    </ul>
    </div>
    <div class='entrytablediv'>
        <p class='recruitsavenote savenote app-feedback-note'></p>
        
        
        <div id='recruittable{{$covs[0]['trap']}}' style='margin-top: 10px;'></div>
        <p style='text-align: center'><button name='recruitsave{{$covs[0]['trap']}}' class=" datasavebutton">儲存</button></p>
        <p class='recruitsavenote savenote app-feedback-note'></p>
        <p style='margin-top:5px;'><button name='clearrecruittable'>清空新增表單</button></p>
    </div>
</div>
    
<div class='slrolltableout text_box seedling-roll-panel' data-seedling-roll-site='{{$covs[0]['trap']}}' style='margin-top: 20px;'>

    <h6>撿到環</h6>
    <hr>
    <div class='entrytablediv'>
        <p class='slrollsavenote savenote app-feedback-note'></p>
        
        
        <div id='slrolltable{{$covs[0]['trap']}}' style='margin-top: 10px;'></div>
        <p style='text-align: center'><span class='slrollsavenote savenote app-feedback-note'></span><button name='slrollsave{{$covs[0]['trap']}}' class="datasavebutton" style='width: 400px;'>儲存</button></p>
        </div>
</div>
   @endif
</div>



{{-- <table id="grid_id"><tbody></tbody></table> --}}

</div>
