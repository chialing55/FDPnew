@extends('layouts/seedling') 
@section('pagejs')

<script type="text/javascript">
  $('.list3').addClass('now');
  $('.list3 hr').css('color', '#91A21C');
</script>

@endsection
@section('rightbox')

@livewire($site.'.seedling-import', ['user' => $user, 'site' => $site])


@endsection