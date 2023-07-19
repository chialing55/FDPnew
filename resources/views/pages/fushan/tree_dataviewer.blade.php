@extends('layouts/tree') 
@section('pagejs')

<script>

  $("#progressTable").tablesorter();
  $('.list5').addClass('now');
  $('.list5 hr').css('color', '#91A21C'); 

    
</script>


@endsection
@section('rightbox')


@livewire($site.'.tree-dataviewer', ['user' => $user, 'site' => $site])
@endsection
