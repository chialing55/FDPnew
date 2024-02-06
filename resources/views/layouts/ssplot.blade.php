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
<script src="{{asset('/js/create-handsontable.js')}}"></script>
<script src="{{asset('/js/ssplot.js')}}"></script>

 @yield('pagejs')

@endsection

 
@section('css')
<link rel="stylesheet" href="{{asset('/css/ssplot.css')}}">


@endsection

@section('headerList')
  <div class='headerlist iflex'>
      <div class='list list1 listlink' type='doc'>相關文件<hr></div>
      <div class='list list6 ' >森林觀測樣區<hr></div>
      <div class='list list4 ' >1.05 ha 樣區<hr></div>

  </div>

@endsection

@section('headerListinner')

      <div class='listinner list4inner'>
          <li class='innerlist list41' type='1ha_note'>資料輸入注意事項<hr></li>
          <li class='innerlist list42 listlink' type='1ha_entry1' >第一次輸入<hr></li>
          <li class='innerlist list43 listlink' type='1ha_entry2' >第二次輸入<hr></li>
          <li class='innerlist list44' type='1ha_compare'>資料比對<hr></li>
          <li class='innerlist list45 listlink' type='1ha_dataviewer'>資料檢視<hr></li>
      </div>

      <div class='listinner list6inner'>
          <li class='innerlist list61 ' type='10m_note'>資料輸入注意事項<hr></li>
          <li class='innerlist list62 listlink' type='10m_entry1' >第一次輸入<hr></li>
          <li class='innerlist list63 listlink' type='10m_entry2' >第二次輸入<hr></li>
          <li class='innerlist list64' type='10m_compare'>資料比對<hr></li>
          <li class='innerlist list65 listlink' type='10m_dataviewer'>資料檢視<hr></li>

      </div>

@endsection

@section('content') 

  <div class="icon">

    <img src="{{asset('/images/小刺山柑_葉_72_300.png')}}" alt="圖案" >
  </div>

@include('includes.header2')
 
<div class='content'>
    
  <div class='right'>

@yield('rightbox')

  </div>

</div>
@endsection

