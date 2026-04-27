@extends('layouts/seedling') 
@section('pagejs')
<script>
window.seedlingConfig = {
  entry: @json($entry),
  user: @json($user),
  plotType: 'fsseedling'
};
window.seedlingRoutes = {
  base: @json(url('/admin/fushan/seedling')),
  recordPdfBase: @json(url('/admin/fushan/seedling/pdf/record')),
  saveCov: @json(route('admin.fushan.seedling.cov.save')),
  saveData: @json(route('admin.fushan.seedling.data.save')),
  saveRecruit: @json(route('admin.fushan.seedling.recruit.save')),
  alternoteSave: @json(route('admin.fushan.seedling.alternote.save')),
  finishBase: @json(url('/admin/fushan/seedling/finish')),
  slrollBase: @json(url('/admin/fushan/seedling/slroll')),
  dataDeleteBase: @json(url('/admin/fushan/seedling/data')),
  alternoteBase: @json(url('/admin/fushan/seedling/alternote')),
  alterDeleteBase: @json(url('/admin/fushan/seedling/alter'))
};
</script>

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
