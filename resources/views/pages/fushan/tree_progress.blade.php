@extends('layouts/tree') 
@section('pagejs')

<script>

  $("#progressTable").tablesorter();
  $('.list3').addClass('now');
  $('.list3 hr').css('color', '#91A21C'); 

    
</script>


@endsection
@section('rightbox')


@livewire($site.'.tree-showprogress', ['user' => $user, 'site' => $site])
@endsection
