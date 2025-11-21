@extends('layouts/webapp') 

@section('title', __('web.title'))

@section('js')
<!-- js -->


@endsection

@section('hero')
    @livewire('web.index-hero')
@endsection
@section('content') 

<div>
    @livewire('web.index')
</div>
@endsection