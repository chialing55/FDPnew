@extends('layouts/webpage')

@section('title', __('web.species_title') . ' - ' . __('web.title'))

@section('css')
    @parent
    <style>
        .page {
            width: min(100% - 2rem, 1280px);
            max-width: 1280px;
        }

        @media (max-width: 767px) {
            .page {
                width: min(100% - 1.5rem, 1280px);
            }
        }
    </style>
@endsection

@section('pagejs')
    <script>
        // var element = document.getElementById("#list1");
        // element.classList.add("now");
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script></script>
@endsection
@section('rightbox')

    @livewire('web.showspecies', ['spcode' => $spcode])
@endsection
