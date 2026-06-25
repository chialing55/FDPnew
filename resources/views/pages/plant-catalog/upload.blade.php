@extends('layouts/plant-catalog')

@section('pagejs')
    <script>
        $('.list1').addClass('now');
        $('.list1 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='flex text_outbox'>
        <div class='text_box'>
            <h2>名錄上傳</h2>
            <hr>
            @livewire('plant-catalog.taiwan-checklist-import')
        </div>
    </div>
@endsection
