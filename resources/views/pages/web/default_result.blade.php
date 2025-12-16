@extends('layouts/webapp')
@section('title', $page->title . ' - ' . __('web.title'))

@section('hero')
    <livewire:web.page-background-hero :slug="$slug" :page="$page" />
@endsection

@section('content')
    {{-- 將 slug 傳給 Livewire --}}
    <livewire:web.page-default :slug="$slug" :page="$page" />
@endsection

@section('pagejs')
    {{-- 需要額外 JS 放這裡 --}}
@endsection
