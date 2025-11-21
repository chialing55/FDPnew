@extends('layouts/webapp')
@section('pagejs')
    <script>
        // var element = document.getElementById("#list1");
        // element.classList.add("now");
    </script>

@endsection
@section('hero')
    @livewire('web.index-hero')
@endsection
@section('content')
    @livewire('web.background-motivation')
@endsection
