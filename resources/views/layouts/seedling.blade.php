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
<script src="{{asset('/js/seedling.js')}}"></script>

 @yield('pagejs')

@endsection

 
@section('css')
<link rel="stylesheet" href="{{asset('/css/seedling.css')}}">


@endsection

@section('headerList')
  <div class='headerlist iflex'>
      <div class='list list1 listlink' type='doc'>相關文件<hr></div>

      <div class='list list4 listlink' >資料輸入<hr></div>
      <div class='list list2 listlink' type='dataviewer'>資料檢視<hr></div>
  </div>

@endsection

@section('headerListinner')
      <div class='listinner list4inner'>
          <li class='innerlist list41 listlink' type='note'>資料輸入注意事項</li>
          <li class='innerlist list42 listlink' type='entry1' >第一次輸入</li>
          <li class='innerlist list43 listlink' type='entry2' >第二次輸入</li>
          <li class='innerlist list44 listlink' type='compare'>資料比對</li>
      @if($user=='chialing')
          <li class='innerlist list45 listlink' type='import'>將資料匯入大表<hr></li>
      @endif

      </div>

@endsection

@section('content') 

  <div class="icon icon2">

    <img src="{{asset('/images/黃杞_苗_72_250.png')}}" alt="圖案">
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

