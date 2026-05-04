@extends('layouts/app2')

@php
    if ($site == 'nanjenshan') {
        $sitec = '南仁山';
    }
@endphp

@section('title', $sitec . '-' . $project . '
-台灣森林動態樣區資料管理系統')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/seedling.css') }}">
@endsection

@section('js')
    <script>
        $('.listlink').on('click', function() {
            let type = $(this).attr('type');
            if (typeof type !== 'undefined') {
                location.href = `/admin/nanjenshan/seedling/${type}`;
            }
        });
    </script>

    @yield('pagejs')
@endsection

@section('headerList')
    <div class='headerlist iflex'>
        <div class='list list1 listlink' type='doc'>相關文件<hr></div>
        <div class='list list2 listlink' type='dataviewer'>資料檢視<hr></div>
        <div class='list list3 listlink' type='download'>資料下載<hr></div>
    </div>
@endsection

@section('content')
    <div class="icon icon2">
        <img src="{{ asset('/images/黃杞_苗_72_250.png') }}" alt="圖案">
    </div>

    @include('includes.header2')

    <div class='content'>
        <div class='right'>
            <div style="display: grid; justify-items: center;">
                @yield('rightbox')
            </div>
        </div>
    </div>
@endsection
