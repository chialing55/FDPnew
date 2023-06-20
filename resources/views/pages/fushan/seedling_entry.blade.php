@extends('layouts/seedling') 
@section('pagejs')
@php 

echo "<script>
const entry = ".$entry.";

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

@livewire($site.'.seedling-showentry', ['entry' =>$entry, 'user' => $user, 'site' => $site])


@endsection