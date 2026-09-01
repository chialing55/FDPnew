@extends('layouts/webpage')
@section('title', '監測植物')
@section('pagejs')
    <script>
        // var element = document.getElementById("#list1");
        // element.classList.add("now");
    </script>

    <script></script>
@endsection
@section('hero')
    <livewire:web.page-background-hero :slug="'plants'" :page="$heroPage" />
@endsection
@section('rightbox')
    <livewire:web.showsplist />
@endsection
