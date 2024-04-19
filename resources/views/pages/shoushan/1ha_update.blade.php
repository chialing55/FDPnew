@extends('layouts/ssplot') 
@section('pagejs')
<script src="{{asset('/js/ss1ha.js')}}"></script>

<script>

  // $("#progressTable").tablesorter();
  $('.list4').addClass('now');
  $('.list4 hr').css('color', '#91A21C'); 

    
</script>
@php 

echo "<script>

const user = '".$user."';

</script>";

@endphp

@endsection
@section('rightbox')


@livewire($site.'.s1ha-update', ['user' => $user, 'site' => $site])
@endsection
