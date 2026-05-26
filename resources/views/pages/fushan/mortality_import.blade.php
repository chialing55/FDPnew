@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list3').addClass('now');
        $('.list3 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    @livewire($site . '.mortality-import', ['user' => $user, 'site' => $site])
@endsection
