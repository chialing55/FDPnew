<div>
    <h2 style='display: inline-block;'>({{$sqx}}, {{$sqy}}) </h2>
    <div class='tablenote'>
@if($record!='無')
        <span style='margin-right: 20px' class='totalnum'></span>
@else
        <span style='margin-right: 20px'> 沒有舊資料</span>
@endif
@php

    $prevshow = (($sqx.$sqy) == $plot2list[0]) ? 'prevhidden' : 'prevshow';
    $nextshow = (($sqx.$sqy) == end($plot2list)) ? 'prevhidden' : 'prevshow';

    $prev = ($prevshow == 'prevshow') ? $plot2list[($nowplotkey[0]-1)] : '00';
    $next = ($nextshow == 'prevshow') ? $plot2list[($nowplotkey[0]+1)] : '00';

@endphp

        <span class='{{$prevshow}}'><a class="a_" wire:click.once="searchSite({{$searchSiteVar}}, {{$prev[0]}},{{$prev[1]}})">上一個樣區</a></span>
        <span class='{{$nextshow}}'><a class="a_" wire:click.once="searchSite({{$searchSiteVar}}, {{$next[0]}},{{$next[1]}})">下一個樣區</a></span>
        <span class='datasavenote savenote'></span>
    </div>
</div>

@if($record!='無')
<div style='display: inline-flex; flex-direction: column; margin-top: 20px;'>
    <div class='pages' style='margin-bottom: 5px'>
        <div class='pagenote'></div>
        <div class='prev'>上一頁</div>
        <div class='next'>下一頁</div>
        <div class='showall'><button>顯示較多資料</button></div>
    </div>

    <div id='datatable{{$tableVar}}' style='' class='fs100' ></div>
    <span class='datasavenote savenote'></span>
    <p style='margin-top:5px; text-align: center;'><button name='datasave{{$tableVar}}' class='datasavebutton'>儲存</button></p>
</div>
<div class='alternotetalbeouter'>
    <h6 class='alterh6'>特殊修改</h6>
    <span style='margin-left: 20px; font-size: 80%; font-weight: 500;'>*只需填寫需修改的資料  {{$alterOtherNote}}</span>

    <p >
        <span class='alterstemid'></span>
        <span class='altersavenote savenote'></span>
    </p>
    <div id='alternotetable' style='margin-top: 5px;' class='fs100' ></div>

    <p style='margin-top:10px; text-align: right;'>
        <button name='alternotesave' class='datasavebutton' style='width: auto;' >儲存</button>

        <button name='deletealternote' class='deletealternotebutton' onclick="deletealternoteButtonClick(this)">刪除此資料</button>
        <button class='close' onclick="$('.alternotetalbeouter').hide(); $('.alternotetable').html();" >X</button>
    </p>
</div>

@endif