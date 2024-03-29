@extends('layouts/tree') 
@section('pagejs')

<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");


  $("#mapTable").tablesorter();
  $('.list7').addClass('now');
  $('.list7 hr').css('color', '#91A21C');


</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
@php 

echo "<script>

const user = '".$user."';

</script>";

@endphp
@endsection
@section('rightbox')

@livewire($site.'.tree-map', ['user' => $user, 'site' => $site])
@endsection
