@extends('layouts/ssplot') 
@section('pagejs')
<script src="{{asset('/js/ss10m.js')}}"></script>

<script>

  // $("#progressTable").tablesorter();
  $('.list6').addClass('now');
  $('.list6 hr').css('color', '#91A21C'); 

    
</script>


@endsection
@section('rightbox')


@livewire($site.'.s10m-dataviewer', ['user' => $user, 'site' => $site])
@endsection
