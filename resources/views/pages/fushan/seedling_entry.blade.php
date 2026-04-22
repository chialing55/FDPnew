@extends('layouts/seedling') 
@section('pagejs')
@php 

echo "<script>
const entry = ".$entry.";
const user = '".$user."';
window.seedlingConfig = {
  entry: ".$entry.",
  user: ".json_encode($user).",
  plotType: 'fsseedling'
};
window.seedlingRoutes = {
  base: ".json_encode(url('/admin/fushan/seedling')).",
  recordPdfBase: ".json_encode(url('/admin/fushan/seedling/pdf/record')).",
  saveCov: ".json_encode(route('admin.fushan.seedling.cov.save')).",
  saveData: ".json_encode(route('admin.fushan.seedling.data.save')).",
  saveRecruit: ".json_encode(route('admin.fushan.seedling.recruit.save')).",
  alternoteSave: ".json_encode(route('admin.fushan.seedling.alternote.save')).",
  finishBase: ".json_encode(url('/admin/fushan/seedling/finish')).",
  slrollBase: ".json_encode(url('/admin/fushan/seedling/slroll')).",
  dataDeleteBase: ".json_encode(url('/admin/fushan/seedling/data')).",
  alternoteBase: ".json_encode(url('/admin/fushan/seedling/alternote')).",
  alterDeleteBase: ".json_encode(url('/admin/fushan/seedling/alter'))."
};
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
