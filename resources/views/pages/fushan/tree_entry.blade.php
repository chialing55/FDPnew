@extends('layouts/tree') 
@section('pagejs')

<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");

$(function() {
  $('.list4').addClass('now');
  $('.list4 hr').css('color', '#91A21C');
})

</script>

@php 

echo "<script>
const entry = ".$entry.";
const user = '".$user."';

</script>";

@endphp
@endsection
@section('rightbox')

@livewire($site.'.tree-showentry', ['entry' =>$entry, 'user' => $user, 'site' => $site])

@endsection
