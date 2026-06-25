@extends('layouts/plant-catalog')

@section('css')
    @parent
    <style>
        .plant-photo-page {
            padding: 30px;
        }
    </style>
@endsection

@section('js')
    @parent
    <script>
        $(function() {
            $('#plantPhotoTable').tablesorter();
        });
    </script>
@endsection

@section('pagejs')
    <script>
        $('.list3').addClass('now');
        $('.list3 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class="plant-photo-page" style="display: grid; justify-items: center;">
        @livewire('plant-catalog.photos')
    </div>
@endsection
