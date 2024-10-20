@extends('layouts/seeds') 
@section('pagejs')
@php 

echo "<script>

const user = '".$user."';
</script>";

@endphp

<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");

$(function() {
  $('.list4').addClass('now');
  $('.list4 hr').css('color', '#91A21C');
})

</script>
@endsection
@section('rightbox')

{{-- <h2>輸入資料</h2> --}}
@livewire($site.'.seeds-updatebackdata', ['user' => $user, 'site' => $site])


@endsection