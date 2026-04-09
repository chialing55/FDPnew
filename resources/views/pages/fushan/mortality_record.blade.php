@extends('layouts/mortality')

@section('pagejs')
    <script>
        $(function() {
            $('.list4').addClass('now');
            $('.list4 hr').css('color', '#91A21C');
        });
    </script>
@endsection

@section('rightbox')
    @livewire($site . '.mortality-record', ['user' => $user, 'site' => $site])
@endsection
