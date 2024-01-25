@extends('layouts/tree') 
@section('pagejs')

<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");

$(function() {
  $('.list6').addClass('now');
  $('.list6 hr').css('color', '#91A21C');
})

</script>

@php 

echo "<script>

const user = '".$user."';

</script>";

@endphp
@endsection
@section('rightbox')

@livewire($site.'.tree-updatetable', ['user' => $user, 'site' => $site])
@endsection
