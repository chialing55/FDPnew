@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list4').addClass('now');
    </script>
@endsection

@section('rightbox')
    @livewire($site . '.mortality-compare')
@endsection
