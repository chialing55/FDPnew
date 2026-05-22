@extends('layouts/seedling')
@section('pagejs')
<script>
window.seedlingConfig = {
  user: @json($user),
  plotType: 'fsseedling'
};
window.seedlingRoutes = {
  base: @json(url('/admin/fushan/seedling')),
  updateData: @json(route('admin.fushan.seedling.update-data')),
  updateDataDelete: @json(route('admin.fushan.seedling.update-data.delete'))
};
</script>
<script type="text/javascript">
  jQuery(".list4").addClass("now");
  jQuery(".list45").addClass("now");
</script>
@endsection

@section('rightbox')
@livewire($site.'.seedling-updatebackdata', ['user' => $user, 'site' => $site])
@endsection
