@extends('layouts/seedling') 
@section('pagejs')
@php 


@endphp

<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");


$('.list2').addClass('now');
$('.list2 hr').css('color', '#91A21C');

</script>
@endsection
@section('rightbox')

{{-- <h2>輸入資料</h2> --}}
@livewire($site.'.seedling-dataviewer', ['user' => $user, 'site' => $site])


@endsection