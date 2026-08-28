@extends('layouts/plant-catalog')

@section('pagejs')
    <script>
        $('.list4').addClass('now');
        $('.list4 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class="flex text_outbox">
        <div class="text_box">
            <h2>福山調查物種整理</h2>
            <hr>
            @livewire('plant-catalog.catalog-maintenance')
        </div>
    </div>
@endsection
