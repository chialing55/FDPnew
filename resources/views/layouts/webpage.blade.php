@extends('layouts/webapp') 

@php


@endphp

@section('title', "樣區植物名錄-福山森林動態樣區")




@section('js')

{{-- <script src="{{asset('/js/jquery-ui.min.js')}}"></script> 
<script src="{{asset('/js/jquery.jqGrid.min.js')}}"></script>
<script src="{{asset('/js/grid.locale-en.js')}}"></script>
<script src="{{asset('/js/jquery.caret-1.5.2.min.js')}}"></script> --}}

<script src="{{asset('/js/web.js')}}"></script>

 @yield('pagejs')

@endsection

 
@section('css')
<link rel="stylesheet" href="{{asset('/css/jquery-ui.css')}}">
<link rel="stylesheet" href="{{asset('/css/ui.jqgrid.css')}}">



@endsection




@section('content') 

  <div class="icon">

  
  </div>

@include('includes.webheader')
 
<div class='content'>
    
  <div class='page'>

@yield('rightbox')

  </div>

</div>
@endsection

