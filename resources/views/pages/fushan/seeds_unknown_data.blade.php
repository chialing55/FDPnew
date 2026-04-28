@extends('layouts/seeds')

@section('pagejs')
<script>
window.seedsConfig = {
  user: @json($user),
  isAdmin: @json((bool) auth()->user()?->is_admin),
  defaultSort: 'trap',
  viewMode: 'unknown-all'
};
window.seedsRoutes = {
  base: @json(url('/admin/fushan/seeds')),
  sectionBase: @json(url('/admin/fushan/seeds')),
  saveDataBase: @json(url("/admin/fushan/seeds/unknown/{$unk}/data")),
  saveData1Base: @json(url("/admin/fushan/seeds/unknown/{$unk}/data1")),
  deleteDataBase: @json(url("/admin/fushan/seeds/unknown/{$unk}/data")),
  finish: @json(url('/admin/fushan/seeds/finish'))
};
</script>

<script>
$(function() {
  $('.list6').addClass('now');
  $('.list6 hr').css('color', '#91A21C');
})
</script>
@endsection

@section('rightbox')

@livewire($site.'.seeds-unknown-data', ['user' => $user, 'site' => $site, 'unk' => $unk])

@endsection
