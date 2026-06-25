@extends('layouts/plant-catalog')

@section('pagejs')
    <script>
        $('.list2').addClass('now');
        $('.list2 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='flex text_outbox'>
        <div class='text_box'>
            <h2>名錄下載</h2>
            <hr>
            @livewire('plant-catalog.checklist-download')
        </div>
    </div>
@endsection
