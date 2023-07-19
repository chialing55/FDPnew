<div>
<h2> 2023 年 每木調查 第 {{$entry}} 次資料輸入</h2>
<div style='margin-top:10px'>
    <p>請先詳閱 <a href="{{asset('/fushan/tree/note')}}"><b>每木輸入注意事項</b></a></p> 
    <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
</div>   
<div style='font-weight: 800; margin-bottom: 20px; display: inline-flex;' >
    <span style='margin-right: 20px;'>選擇要輸入的樣方</span>
    <form wire:submit.prevent='submitForm'>
        <select name="qx" class="fs100 entryqx" wire:model.defer='qx' style='height:25px;'>
        @for ($i=0; $i<25;$i++)
        <option value="{{$i}}">{{$i}} 
        </option>
         @endfor
        </select>-<select name="qy" class="fs100" wire:model.defer='qy' style='height:25px;'>
        @for ($i=0; $i<25;$i++)
        <option value="{{$i}}">{{$i}} 
        </option>
         @endfor
        </select>
        <button type="submit" style='margin-left: 20px;'>送出</button>
    </form>
    <span style='padding-left:20px'>{{$entrynote}}</span>
        @if ($record && $entrynote=='')
            @if($qy>0)
            <span style='padding-left:20px'><a class='a_' wire:click.once="searchsite({{$qx}}, {{$qy-1}}, 1, 1)">上一個樣方</a></span>
            @endif
            @if($qy<25)
              <span style='padding-left:20px'><a class='a_' wire:click.once="searchsite({{$qx}}, {{$qy+1}}, 1, 1)">下一個樣方</a></span>
            @endif  
        @endif

</div> 

@if($record && $record!='')
<div>
<div id='simplenote' class='text_box'>
<ul>
<li><b>輸入資料後需按 <button>儲存</button> ，才能確實將資料儲存。</b></li>
<li>日期格式： YYYY-MM-DD。每筆資料皆需輸入日期，<b>日期為 0000-00-00 者視同未輸入</b>。</li>
<li>status 為 0,-1,-2,-3，則 dbh 需為0，且 code 不得有值。tatus 為空值，則 dbh 不得為 0。</li>
<li>dbh/h高 必須<b>大於或等於</b>上次調查，或勾選縮水。</li>
<li>若 code 包含 C，則 POM 不得同於前次 POM。code R 只能出現在分支。<span class='line'>code 代碼間可共存</span>，多碼時照字母排列，<span class='line'>中間不留空格</span>。</li>
<li>POM 更新，code 欄需有 C 。若是原始資料錯誤，請在「特殊修改<i class='fa-regular fa-note-sticky'></i>」更新。</li>
<li>note： TAB=#。統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>，數字後留一格空白。先確認原始 note，加句號，再輸入本次note。</li>
<li>20x，20y，5x，5y，tag，b，csp，POM 等欄位需要修改時，請至「特殊修改<i class='fa-regular fa-note-sticky'></i>」填寫。</li>
<li>新樹資料可以修改或刪除。</li></ul>
</div>
@php

$fileqx=str_pad($qx, 2, '0', STR_PAD_LEFT);
$fileqy=str_pad($qy, 2, '0', STR_PAD_LEFT);
$filesqx=$fileqx.$fileqy;

@endphp

<div class='text_box'>第四次調查電子檔：<a href='{{asset('/fs_census4_scanfile/'.$fileqx.'/old/'.$filesqx.'_old.pdf')}}' target="_blank">舊樹</a>  <a href='{{asset('/fs_census4_scanfile/'.$fileqx.'/new/'.$filesqx.'_new.pdf')}}' target="_blank">新樹</a></div>
@php
// print_r($record[1][1]);
$plot2list=array("11","12","22","21","13","14","24","23","33","34","44","43","31","32","42","41");
$nowplotkey=array_keys($plot2list, $sqx.$sqy);
$nowplot=$plot2list[$nowplotkey[0]];

@endphp

<div class='text_box'>
    <div class='entrytablediv'>
        <h2 style='display: inline-block;'>({{$sqx}}, {{$sqy}}) </h2>
        <div class='tablenote'>
            @if($record!='無')
            <span style='margin-right: 20px' class='totalnum'></span>
            @else
            <span style='margin-right: 20px'> 沒有舊資料</span>
            @endif
        @if(($sqx.$sqy)!=$plot2list[0])
        @php 
            $prev=$plot2list[($nowplotkey[0]-1)];
        @endphp
            <span style='margin-left:30px'><a class="a_" wire:click.once="searchsite({{$qx}}, {{$qy}}, {{$prev[0]}}, {{$prev[1]}})">上一個樣區</a></span>
        @endif
        @if((($sqx.$sqy)!=$plot2list[15]))
        @php 
            $next=$plot2list[($nowplotkey[0]+1)];
        @endphp
            <span style='margin-left: 30px;'><a class="a_" wire:click.once="searchsite({{$qx}}, {{$qy}}, {{$next[0]}}, {{$next[1]}})">下一個樣區</a></span>
        @endif
        </div>


        @if($record!='無')

        
        <span class='datasavenote savenote'>

        </span>
        <div class='pages' style='margin-top: 5px;'>
           <div class='pagenote'></div>
           <div class='prev'>上一頁</div>
           <div class='next'>下一頁</div>
       </div>
        <div id='datatable{{$qx}}{{$qy}}{{$sqx}}{{$sqy}}' style='margin-top: 20px;' class='fs100' ></div>


        <p style='margin-top:5px;'><button name='datasave{{$qx}}{{$qy}}{{$sqx}}{{$sqy}}' >儲存</button></p>
    
        <div class='alternotetalbeouter'>
            <h6 class='alterh6'>特殊修改</h6>
            <p ><span class='alterstemid'></span>
            <span class='altersavenote savenote'></span></p>
            <div id='alternotetable' style='margin-top: 5px;' class='fs100' ></div>


            <p style='margin-top:10px; text-align: right;'><button name='alternotesave' >儲存</button>

            <button name='deletealternote' class='deletealternotebutton' onclick="deletealternoteButtonClick(this)">刪除此資料</button>
            <button class='close' onclick="$('.alternotetalbeouter').hide(); $('.alternotetable').html();" >X</button>

            </p>


        </div>

    @endif
</div>
</div>    

<div style='margin-left: 30px;'>
<button class='recruit' onclick="$('.recruittableout').toggle();"> 新增樹與漏資料樹</button>
</div>
 

<div class=' text_box recruittableout' style='margin-top: 20px;'>

   <h6>新增樹與漏資料樹</h6>
   <hr>
   <div id='simplenote' class='text_box'>
        <ul>
            <li>dbh - 新增樹的dbh<b>必須 ≥ 1</b>。</li>
            <li>新增狀態 - 預設為新增。若為漏資料的樹，請記得點選。</li>
            <li>樹蕨的<b>h低</b>請填入<b>pom</b>欄位。</li>
            <li>未通過檢查以致無法儲存的資料將保留在輸入表單中。</li>
            <li>表單中間有空行並不影響儲存。</li>
        </ul>
    </div>
    <div class='entrytablediv'>
        <p class='recruitsavenote savenote'></p>
        
        <p style='text-align: right;'><button name='recruitsave{{$qx}}{{$qy}}{{$sqx}}{{$sqy}}' class="save2">儲存</button></p>
        <div id='recruittable{{$qx}}{{$qy}}{{$sqx}}{{$sqy}}' style='margin-top: 10px;'></div>
        <p style='margin-top:5px;'><button name='clearrecruittable' class="save2">清空新增表單</button></p>
    </div>
</div>


@endif


</div>