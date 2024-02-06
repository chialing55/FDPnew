@extends('layouts/app2') 

@php
  if ($site=='fushan'){
    $sitec='福山';
  }



@endphp

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
<script src="{{asset('/js/seeds.js')}}"></script>

 @yield('pagejs')

@endsection

 
@section('css')
<link rel="stylesheet" href="{{asset('/css/seeds.css')}}">


@endsection

@section('headerList')
  <div class='headerlist iflex'>
      <div class='list list1 listlink' type='doc'>相關文件<hr></div>
      <div class='list list4 listlink' >資料輸入<hr></div>
      <div class='list list6 listlink' >資料查詢<hr></div>
  </div>

@endsection

@section('headerListinner')
      <div class='listinner list4inner'>
          <li class='innerlist list41 listlink' type='note'>資料輸入注意事項<hr></li>
          <li class='innerlist list42 listlink' type='entry'>資料輸入<hr></li>
          <li class='innerlist list43 listlink' type='updateBackData'>檢視/更新資料<hr></li>
          {{-- <li class='innerlist list45 listlink' type='import'>將資料匯入大表<hr></li> --}}


      </div>

      <div class='listinner list6inner'>
        <li class='innerlist list62 listlink' type='websplist'>物種名錄<hr></li>
        <li class='innerlist list61 listlink' type='showdata'>歷年資料查詢<hr></li>
        <li class='innerlist list62 listlink' type='unknown'>UNKNOWN<hr></li>



      </div>

@endsection

@section('content') 

  <div class="icon">

    <img src="{{asset('/images/山龍眼_花果_72_300.png')}}" alt="圖案">
  </div>

@include('includes.header2')
 
<div class='content'>
    
  <div class='right'>

@yield('rightbox')

  </div>

</div>
@endsection

