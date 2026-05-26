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
 @yield('pagejs')
<script src="{{ asset('/js/create-handsontable.js') }}?v={{ filemtime(public_path('js/create-handsontable.js')) }}"></script>
<script src="{{ asset('/js/seedling.js') }}?v={{ filemtime(public_path('js/seedling.js')) }}"></script>

@endsection

 
@section('css')
<link rel="stylesheet" href="{{ asset('/css/seedling.css') }}?v={{ filemtime(public_path('css/seedling.css')) }}">


@endsection

@section('headerList')
  <div class='headerlist iflex'>
      <div class='list list1 listlink' type='doc'>相關文件<hr></div>

      <div class='list list4 listlink' >資料輸入<hr></div>
      <div class='list list2 listlink' type='dataviewer'>資料檢視<hr></div>
      @if((int) (auth()->user()?->is_admin ?? 0) === 1)
      <div class='list list3 listlink admin-only-link'>資料處理<hr></div>
      @endif
  </div>

@endsection
@section('headerListinner')

      @php($isAdmin = (int) (auth()->user()?->is_admin ?? 0) === 1)
      <div class='listinner list4inner' style='display:none;'>
          <li class='innerlist list41 listlink' type='note'>資料輸入注意事項</li>
          <li class='innerlist list42 listlink' type='entry1' >第一次輸入</li>
          <li class='innerlist list43 listlink' type='entry2' >第二次輸入</li>
          <li class='innerlist list44 listlink' type='compare'>資料比對<hr></li>
      </div>

      @if($isAdmin)
      <div class='listinner list3inner' style='display:none;'>
          <li class='innerlist list31 listlink admin-only-link' type='update'>資料修改</li>
          <li class='innerlist list32 listlink admin-only-link' type='import'>將資料匯入大表</li>
          <li class='innerlist list33 listlink admin-only-link' type='download'>下載資料<hr></li>
      </div>
      @endif

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

