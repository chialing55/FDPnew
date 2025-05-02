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
  {{-- <link rel="stylesheet" href="{{asset('/css/index.css')}}"> --}}
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



@yield('content')

@yield('footer')

</div>


</body>

{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
<script src="{{asset('/js/jquery-3.6.0.min.js')}}"></script>
<script src="{{asset('/js/handsontable/dist/handsontable.full.js')}}"></script>
<script src="{{asset('/js/jquery.tablesorter.min.js')}}"></script>
<script src="{{asset('/js/jquery.tablesorter.widgets.min.js')}}"></script>
<script>
    $('.back').on('click', function(){
        location.href=`/choice`;
    })


    function handleHoverEvents(selector, innerSelector) {
      $(selector + ', ' + innerSelector).on('mouseenter', function() {
        $(innerSelector).css('display', 'inline-flex');
        $(selector).css({'color': '#fff', 'background-color': '#91A21C'}); 
        $('.now hr').css('color', 'transparent');
      }).on('mouseleave', function() {
        $(innerSelector).hide();
        $(selector).css({'color': '', 'background-color': ''}); 
        $('.now hr').css('color', '#91A21C');
      });
    }
    
</script>
@livewireScripts



@yield('js')

    @php

    @endphp

</html>