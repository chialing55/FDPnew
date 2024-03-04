<div>
    <h2 style='display: inline-block;'>({{$sqx}}, {{$sqy}}) </h2>
    <div class='tablenote'>
@if($record!='無')
        
@else
        <span style='margin-right: 20px'> 沒有舊資料</span>
@endif
@include('includes.str-tree-pages')
<span class='datasavenote savenote'></span>
    </div>
</div>
