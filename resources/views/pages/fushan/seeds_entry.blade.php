@extends('layouts/seeds') 
@section('pagejs')
<script>
window.seedsConfig = {
  user: @json($user),
  isAdmin: @json((bool) auth()->user()?->is_admin)
};
window.seedsRoutes = {
  base: @json(url('/admin/fushan/seeds')),
  sectionBase: @json(url('/admin/fushan/seeds')),
  saveDataBase: @json(url('/admin/fushan/seeds/data')),
  saveData1Base: @json(url('/admin/fushan/seeds/data1')),
  deleteDataBase: @json(url('/admin/fushan/seeds/data')),
  finish: @json(url('/admin/fushan/seeds/finish'))
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

{{-- <h2>輸入資料</h2> --}}
@livewire($site.'.seeds-showentry', ['user' => $user, 'site' => $site])


@endsection
