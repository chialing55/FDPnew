@extends('layouts/app2')

@php
    $sitec = '植物照片編輯';
    $project = '植物名錄';
    $user = auth()->user()?->name ?? '';
@endphp

@section('title', '植物照片編輯-台灣森林動態樣區資料管理系統')

@section('css')
    <style>
        .plant-photo-page {
            padding: 30px;
        }

        .plant-photo-header-link,
        .plant-photo-header-link:hover,
        .plant-photo-header-link:visited {
            color: inherit;
            text-decoration: none;
        }
    </style>
@endsection

@section('js')
    <script>
        $(function() {
            $('#plantPhotoTable').tablesorter();
        });
    </script>
@endsection

@section('headerList')
    <div class="headerlist iflex">
        <a class="plant-photo-header-link" href="{{ route('admin.plant-photos.index') }}">
            <div class="list list1 now">植物名錄<hr></div>
        </a>
    </div>
@endsection

@section('content')
    <div class="icon">
        <img src="{{ asset('/images/紅楠_葉_72_300.png') }}" alt="圖案">
    </div>

    @include('includes.header2')

    <div class="content">
        <div class="right">
            <div class="plant-photo-page" style="display: grid; justify-items: center;">
                @livewire('plant-photos.index')
            </div>
        </div>
    </div>
@endsection
