@extends('layouts.geo-tree-survey')

@section('pagejs')
    <script>
        $('.list4').addClass('now');
        $('.list4 hr').css('color', '#91A21C');
        $('.list4{{ (int) $entry + 1 }}').addClass('now');
    </script>
@endsection

@section('rightbox')
    @livewire($site . '.geo-tree-survey-showentry', ['entry' => $entry, 'user' => $user, 'site' => $site])
@endsection
