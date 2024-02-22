<div >
<h2> 2023 年 每木調查 第 {{$entry}} 次資料輸入</h2>
<div style='margin-top:10px'>
    <p>請先詳閱 <a href="{{asset('/fushan/tree/note')}}"><b>每木輸入注意事項</b></a></p> 
    <p> 輸入者 {{$user}}，輸入日期 {{date("Y-m-d")}}</p>
</div>   
<div style='font-weight: 800; margin-bottom: 20px; display: inline-flex;' >
    <div style='display: block;'>
    <div style='display: inline-flex;'>
    <span style='margin-right: 20px;'>選擇要輸入的樣方</span>
    <form wire:submit.prevent='submitForm'>
        <select name="qx" class="fs100 entryqx" wire:model.defer='qx' style='height:25px;'>
        @for ($i=0; $i<25;$i++)
                @php 
                    if (!in_array($i, $updatelist)){
                        echo "<option value=".$i.">".$i."</option>";
                    } 
                @endphp
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
    </div>
    <div style='margin-top: 20px; margin-left: 120px;'>
@if ($record && $entrynote=='')
@php
   if ($qy==0){$prevshow="prevhidden"; $nextshow='prevshow';} 
   else if ($qy==25){$prevshow='prevshow'; $nextshow='prevhidden';}
   else {$prevshow='prevshow'; $nextshow='prevshow';}
@endphp
            <span class='{{$prevshow}}'><a class='a_' wire:click.once="searchSite({{$qx}}, {{$qy-1}}, 1, 1)">上一個樣方</a></span>
            <span class='{{$nextshow}}'><a class='a_' wire:click.once="searchSite({{$qx}}, {{$qy+1}}, 1, 1)">下一個樣方</a></span>

@endif
    </div>
    </div>
@if($record && $record!='')
@php
// print_r($record[1][1]);
$plot2list=array("11","12","22","21","13","14","24","23","33","34","44","43","31","32","42","41");
$nowplotkey=array_keys($plot2list, $sqx.$sqy);
$nowplot=$plot2list[$nowplotkey[0]];

@endphp

<div style='font-weight: 800; display: inline-flex; margin-left: 50px; padding-left:20px; border-left: 1px solid #777;'>
    <span style='margin-right: 20px;'>選擇小樣區</span>     
{{--     <form wire:submit.prevent='submitsqxForm'>
        <select name="sqx" class="fs100 entrysqx" wire:model.defer='sqx' style='height:25px;'>
        @for ($i=1; $i<5;$i++)
        <option value="{{$i}}">{{$i}} 
        </option>
         @endfor
        </select>-<select name="sqy" class="fs100" wire:model.defer='sqy' style='height:25px;'>
        @for ($i=1; $i<5;$i++)
        <option value="{{$i}}">{{$i}} 
        </option>
         @endfor
        </select>
        <button type="submit" style='margin-left: 20px;'>送出</button>
    </form> --}}

    
        <div wire:ignore style='line-height: 1.5;'> 
            @for($j=4; $j>0; $j--)
                @for($i=1; $i<5; $i++)
            @php
            if ($i==$sqx && $j==$sqy)
            {$class='selected';} else {$class='';}
            @endphp            
                <div class="plottable2 plot{{$i}}{{$j}} {{$class}}" wire:click.once="submitsqxForm({{$i}}, {{$j}})">{{$i}}, {{$j}}</div>
                @if($i==4)<br>@endif
                @endfor
            @endfor
        </div>
    

</div>
@endif
</div> 

@if($record && $record!='')
<div>

<div id='simplenote' class='text_box'>
<div class=toggletip onclick="$('.tip').toggle();$('.tiptriangle').toggleClass('tiptriangletoggled');" style='cursor: pointer; margin:10px'><div class="tiptriangle">&#9654;</div><b>輸入提醒</b></div>
<div class='tip' style='display: none;'>
    <ul >

    <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b>請確實依照紙本資料輸入，以減少兩次輸入的不一致。</li>
    <li>日期格式： YYYY-MM-DD。每筆資料皆需輸入日期，<b>日期為 0000-00-00 / 空白者視同未輸入</b>。</li>
    <li>status 為 0(全株死亡),-1(全株失蹤),-2(全株 dbh < 1 cm),-3(枝幹死亡)，則 dbh 需為0，且 code 不得有值。status 為空值，則 dbh 不得為 0。</li>
    <li>dbh/h高 必須<b>大於或等於</b>上次調查，或勾選縮水。</li>
    <li>code：CIPR。若 code 包含 C，則 POM 不得同於前次 POM。code R 只能出現在分支。<b>code 代碼間可共存</b>，多碼時照字母排列，<b>中間不留空格</b>。</li>
    <li>POM 更新，code 欄需有 C 。若是原始資料錯誤，請在「特殊修改<i class='fa-regular fa-note-sticky'></i>」更新。</li>
    <li>note： TAB=#。統一使用<b>「中文」標點符號</b>。<b>「半形」英文符號</b>。<b>「半形」阿拉伯數字</b>，數字後留一格空白。先確認原始 note，加「。」，再輸入本次note。不同類型 note 間用「。」分隔。</li>
    <li>20x，20y，5x，5y，tag，b，csp，POM 等欄位需要修改時，請至「特殊修改<i class='fa-regular fa-note-sticky'></i>」填寫。<b>只需填寫需修改的部分。</b></li>
    <li>如需修改位置資訊及種類，寫於主幹的特殊修改即可。</li>
    <li>若調查後的 dbh < 1 cm，請在表格內填寫 1，再至「特殊修改<i class='fa-regular fa-note-sticky'></i>」的 dbh(<1) 欄位填寫正確之調查資料。</li>
    <li>新樹資料可以修改或刪除。</li>
    <li><b>每一20×20樣方輸入完成後，請按下<button class='datasavebutton' style='width: auto;'>輸入完成</button>。</b>檢查通過後，即會在資料輸入進度表中留下紀錄。若有更新資料，則需重新按<button class='datasavebutton' style='width: auto;'>輸入完成</button>，以再次檢查並更新完成紀錄。</li>
    <li><a href='https://bit.ly/3YcMFY4' target="_blank">每木調查除錯進度統整表</a></li>
    </ul>
</div>
</div>
@php

$fileqx=str_pad($qx, 2, '0', STR_PAD_LEFT);
$fileqy=str_pad($qy, 2, '0', STR_PAD_LEFT);
$filesqx=$fileqx.$fileqy;
$searchSiteVar=$qx.",".$qy;
$tableVar=$qx.$qy.$sqx.$sqy;
$alterOtherNote="*如為換號請在號碼後方備註，如:156601(換號)";

@endphp

<div class='text_box'>第四次調查電子檔：<a href='{{asset('/fs_census4_scanfile/'.$fileqx.'/old/'.$filesqx.'_old.pdf')}}' target="_blank">舊樹</a>  <a href='{{asset('/fs_census4_scanfile/'.$fileqx.'/new/'.$filesqx.'_new.pdf')}}' target="_blank">新樹</a></div>



<div class='text_box'>
    <div class='entrytablediv'>


@include('includes.str-tree-entrytable')
    </div>
</div>    

<div style='margin-left: 30px;'>
<button class='recruit recruitbutton' onclick="$('.recruittableout').toggle();">新增樹與漏資料樹</button>
</div>


<div class=' text_box recruittableout' style='margin-top: 20px;'>

   <h6>新增樹與漏資料樹</h6>
   <hr>
   <div id='simplenote' class='text_box'>
        <ul>
            <li><b>輸入資料後需按 <button class='datasavebutton' style='width: auto;'>儲存</button> ，才能確實將資料儲存。</b></li>
            <li>dbh - 新增樹的 dbh <b>必須 ≥ 1</b>。</li>
            <li>新增狀態 - 預設為新增。若為漏資料的樹，請記得點選。</li>
            <li>樹蕨的<b> h低 </b>請填入<b> pom </b>欄位。</li>
            <li>未通過檢查以致無法儲存的資料將保留在輸入表單中。已儲存的資料可按右鍵以刪除。</li>
            <li>若需新增植物種類，請洽資料管理員。</li>
            
        </ul>
    </div>
@include('includes.str-recruit-entrytable')
</div>
<div style='margin: 30px 0 0 30px ;'>
<button class='finish finishbutton' onclick="finish({{$qx}}, {{$qy}}, {{$entry}})">輸入完成</button>
<span class='finishnote savenote'></span>
</div> 
@else
<div class='text_box'>

    <p><h6>已完成資料比對、上傳檔案，並匯入大表的樣線，將無法再次進行輸入。</h6>
        <ul>
            <li>如需新增，請至 <a href="{{asset('/fushan/tree/addData')}}">新增資料</a> 頁面。</li>
            <li>如需修改，請通知資料管理員。</li>
        </ul>

    </p>
    <hr>
@include('includes.fstree-census5-progress')
</div>

@endif


</div>