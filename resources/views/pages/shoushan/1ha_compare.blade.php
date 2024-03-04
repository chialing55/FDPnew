@extends('layouts/ssplot') 
@section('pagejs')
<script src="{{asset('/js/ss10m.js')}}"></script>

<script>

  // $("#progressTable").tablesorter();
  $('.list4').addClass('now');
  $('.list4 hr').css('color', '#91A21C'); 

    
</script>


@endsection
@section('rightbox')


@livewire($site.'.s1ha-compare', ['user' => $user, 'site' => $site])
@endsection
