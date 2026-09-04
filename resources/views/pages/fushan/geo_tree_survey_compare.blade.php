@extends('layouts.geo-tree-survey')

@section('pagejs')
    <script>
        $('.list4').addClass('now');
        $('.list4 hr').css('color', '#91A21C');
        $('.list44').addClass('now');
    </script>
@endsection

@section('rightbox')
    <div class="flex text_outbox">
        @livewire('fushan.geo-tree-survey-compare')
    </div>
@endsection
