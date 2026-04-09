@extends('layouts/mortality')

@section('pagejs')
    @php
        echo "<script>const entry = " . json_encode($entry) . "; const user = " . json_encode($user) . ";</script>";
    @endphp

    <script>
        $(function() {
            $('.list4').addClass('now');
            $('.list4 hr').css('color', '#91A21C');
        });
    </script>
@endsection

@section('rightbox')
    @livewire($site . '.mortality-showentry', ['entry' => $entry, 'user' => $user, 'site' => $site])
@endsection
