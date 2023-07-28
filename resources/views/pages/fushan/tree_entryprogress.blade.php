@extends('layouts/tree') 
@section('pagejs')

<script>

  $("#progressTable").tablesorter();
  $('.list4').addClass('now');
  $('.list4 hr').css('color', '#91A21C'); 

    
</script>


@endsection
@section('rightbox')


@livewire($site.'.tree-showentryprogress', ['user' => $user, 'site' => $site])
@endsection
