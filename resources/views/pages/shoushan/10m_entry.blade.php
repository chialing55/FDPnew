@extends('layouts/ssplot') 
@section('pagejs')

<script src="{{asset('/js/ss10m.js')}}"></script>
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
const entry = ".$entry.";
const user = '".$user."';

</script>";

@endphp
@endsection
@section('rightbox')

@livewire($site.'.s10m-showentry', ['entry' =>$entry, 'user' => $user, 'site' => $site])
@endsection
