@extends('layouts/nanjenshan-seedling')

@section('pagejs')
    <script>
        $('.list2').addClass('now');
        $('.list2 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    @livewire($site . '.seedling-dataviewer', ['user' => $user, 'site' => $site])
@endsection
