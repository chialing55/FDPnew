@extends('layouts/seeds') 
@section('pagejs')
@php 


@endphp

<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");

$(function() {
  $('.list6').addClass('now');
  $('.list6 hr').css('color', '#91A21C');
})

</script>
@endsection
@section('rightbox')

{{-- <h2>輸入資料</h2> --}}
@livewire($site.'.seeds-dataviewer', ['user' => $user, 'site' => $site])


@endsection