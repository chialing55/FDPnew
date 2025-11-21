@extends('layouts/webapp')

@php

@endphp

@section('js')

    <script src="{{ asset('/js/web.js') }}"></script>

    @yield('pagejs')

@endsection


@section('css')
    <link rel="stylesheet" href="{{ asset('/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/ui.jqgrid.css') }}">


@endsection


@section('content')
    <div class='content'>
        <div class='page'>
            @yield('rightbox')
        </div>
    </div>
@endsection
