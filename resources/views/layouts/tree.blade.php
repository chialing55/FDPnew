@extends('layouts/app2') 

@php
  if ($site=='fushan'){
    $sitec='福山';
  }



@endphp
@section('header_js')

@endsection

@section('title', $sitec."-".$project."
-台灣森林動態樣區資料管理系統")


@section('header_js')

@endsection

@section('js')
<!-- js -->
{{-- <script src="{{asset('/js/jquery-ui.min.js')}}"></script> --}}
{{-- <script src="{{asset('/js/jquery.jqGrid.min.js')}}"></script>
<script src="{{asset('/js/grid.locale-en.js')}}"></script> --}}
{{-- <script src="{{asset('/js/jquery.caret-1.5.2.min.js')}}"></script> --}}
<script src="{{asset('/js/create-handsontable.js')}}"></script>
<script src="{{asset('/js/tree-map.js')}}"></script>
<script src="{{asset('/js/fstree.js')}}"></script>


@yield('pagejs')

@endsection

@section('css')
<link rel="stylesheet" href="{{asset('/css/fstree.css')}}">


@endsection

@section('headerList')
  <div class='headerlist iflex'>
      <div class='list list1 listlink' type='doc'>相關文件<hr></div>
      <div class='list list3 listlink' type='progress'>調查進度<hr></div>
      <div class='list list4 ' >資料輸入<hr></div>

      <div class='list list5 listlink' type='dataviewer' >資料檢視<hr></div>
     
      <div class='list list6 ' type='modify'>資料處理<hr>
      </div>
  {{--         <div class='listinner list6inner'>
            <li>每木調查資料修改流程</li>
            <li>更新census5資料表</li>
            <li>後端資料更正</li>
            <li>新增資料</li>
          </div> --}}
      
      <div class='list list7 listlink' type='map'>植株位置輸入<hr></div>
  </div>

@endsection

@section('headerListinner')
      <div class='listinner list4inner'>
          <li class='innerlist list41 listlink' type='note'>資料輸入注意事項<hr></li>
          <li class='innerlist list42 listlink' type='entry1' >第一次輸入<hr></li>
          <li class='innerlist list43 listlink' type='entry2' >第二次輸入<hr></li>
          <li class='innerlist list44 listlink' type='compare'>資料比對<hr></li>
          <li class='innerlist list45 listlink' type='entryprogress'>資料輸入進度<hr></li>
      </div>
      <div class='listinner list6inner'>
          <li class='innerlist list61 listlink' type='modifyPathway'>每木調查資料修改流程<hr></li>
           @if($user=='chialing')
          <li class='innerlist list62 listlink' type='updateTable' >更新census5資料表<hr></li>
          <li class='innerlist list63 listlink' type='updateBackData' >後端資料更正<hr></li>
           @endif
          <li class='innerlist list64 listlink' type='addData'>新增資料<hr></li>

      </div>
@endsection

@section('content') 

  <div class="icon">

    <img src="{{asset('/images/長尾栲_葉_72_300.png')}}" alt="圖案">
  </div>

@include('includes.header2')
 
<div class='content'>
    
  <div class='right'>
<div style="display: grid; justify-items: center;">
@yield('rightbox')
</div>
  </div>

</div>
@endsection

