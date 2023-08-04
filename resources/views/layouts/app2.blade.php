@php
date_default_timezone_set("Asia/Taipei");
@endphp
<!DOCTYPE html>
<html lang="tw">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="language" content="zh-TW" />  
  <link rel="alternate" href="" hreflang="en" /> 
 @livewireStyles
	<title>@yield('title')</title>
  @yield('meta')

  <link rel="stylesheet" href="{{asset('/js/handsontable/dist/handsontable.full.css')}}">
  <link rel="stylesheet" href="{{asset('/css/index.css')}}">
  <link rel="stylesheet" href="{{asset('/css/style.css')}}">
  <link rel="stylesheet" href="{{asset('/css/theme.green.min.css')}}">
  <link href="{{ asset('/css/all.min.css') }}" rel="stylesheet">  {{--Font Awesome --}}
  @yield('css')


@yield('header_js')
@livewireStyles
</head>
<body>
@livewireStyles
<div class='main'>

  <div class="icon">
    <img src="{{asset('/images/header_tree.png')}}" alt="圖案">
  </div>

@yield('content')

	<footer>
      <div id="header_text" class='fc-w flex' style='font-size: 14px;' >

        <p>如有任何問題，請洽 @kris1014，或填寫 <a href='https://forms.gle/FKDWx2rmLRyE8ED1A' target="_blank">輸入系統錯誤表單</a></p>
        @if(session('latest_update'))
    <p style='margin-left: 100px;'>更新日期：{{ session('latest_update') }}</p>
        @endif

        

      </div>

	</footer>

</div>


</body>

{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
<script src="{{asset('/js/jquery-3.6.0.min.js')}}"></script>
<script src="{{asset('/js/handsontable/dist/handsontable.full.js')}}"></script>
<script src="{{asset('/js/jquery.tablesorter.min.js')}}"></script>
<script src="{{asset('/js/jquery.tablesorter.widgets.min.js')}}"></script>
@livewireScripts



@yield('js')

    @php

    @endphp

</html>