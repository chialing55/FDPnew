@extends('layouts/app2') 

@php
  if ($site=='shoushan'){
    $sitec='壽山';
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
<script src="{{asset('/js/ssplot.js')}}"></script>

 @yield('pagejs')

@endsection

 
@section('css')
<link rel="stylesheet" href="{{asset('/css/ssplot.css')}}">


@endsection

@section('headerList')
  <div class='headerlist iflex'>
      <div class='list list1 listlink' type='doc'>相關文件<hr></div>
      <div class='list list4 ' >1.05樣區資料輸入<hr></div>
      <div class='list list6 ' >森林觀測樣區資料輸入<hr></div>
  </div>

@endsection

@section('headerListinner')
      <div class='listinner list4inner'>
          <li class='innerlist list41 listlink' type='note'>資料輸入注意事項<hr></li>
          <li class='innerlist list42 listlink' type='1.05_entry1' >第一次輸入<hr></li>
          <li class='innerlist list43 listlink' type='1.05_entry2' >第二次輸入<hr></li>
          <li class='innerlist list44 listlink' type='1.05_compare'>資料比對<hr></li>

      </div>

      <div class='listinner list6inner'>
          <li class='innerlist list61 listlink' type='note'>資料輸入注意事項<hr></li>
          <li class='innerlist list62 listlink' type='fp_entry1' >第一次輸入<hr></li>
          <li class='innerlist list63 listlink' type='fp_entry2' >第二次輸入<hr></li>
          <li class='innerlist list64 listlink' type='fp_compare'>資料比對<hr></li>

      </div>

@endsection

@section('content') 

  <div class="icon icon2">

    <img src="{{asset('/images/小刺山柑_葉_72_300.png')}}" alt="圖案">
  </div>

@include('includes.header2')
 
<div class='content'>
    
  <div class='right'>

@yield('rightbox')

  </div>

</div>
@endsection

