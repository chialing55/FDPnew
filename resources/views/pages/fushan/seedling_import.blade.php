@extends('layouts/seedling') 
@section('pagejs')

<script type="text/javascript">
  $('.list5').addClass('now');
</script>

@endsection
@section('rightbox')

@livewire($site.'.seedling-import', ['user' => $user, 'site' => $site])


@endsection