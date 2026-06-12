@extends('layouts/app2')

@php
    if ($site == 'fushan') {
        $sitec = '福山';
    }
@endphp

@section('title', $sitec . '-' . $project . '
-台灣森林動態樣區資料管理系統')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/seedling.css') }}?v={{ filemtime(public_path('css/seedling.css')) }}">
@endsection

@section('js')
    <script>
        $('.listlink').on('click', function() {
            let type = $(this).attr('type');
            if (typeof type !== 'undefined') {
                location.href = `/admin/fushan/mortality/${type}`;
            }
        });

        handleHoverEvents('.list4', '.list4inner');
        handleHoverEvents('.list3', '.list3inner');
    </script>

    @yield('pagejs')
@endsection

@section('headerList')
    @php($isAdmin = (int) (auth()->user()?->is_admin ?? 0) === 1)
    <div class='headerlist iflex'>
        <div class='list list1 listlink' type='doc'>相關文件<hr></div>
        <div class='list list4 listlink'>資料輸入<hr></div>
        <div class='list list2 listlink' type='dataviewer'>資料檢視<hr></div>
        <div class='list list5 listlink' type='download'>資料下載<hr></div>
        @if ($isAdmin)
            <div class='list list3 listlink admin-only-link'>資料處理<hr></div>
        @endif
    </div>
@endsection
@section('headerListinner')
    <div class='listinner list4inner' style='display:none;'>
        <li class='innerlist list41 listlink' type='census'>調查年度</li>
        <li class='innerlist list42 listlink' type='survey-import'>匯入調查資料</li>
        <li class='innerlist list43 listlink' type='note'>輸入注意事項</li>
        <li class='innerlist list44 listlink' type='entry1'>第一次輸入</li>
        <li class='innerlist list45 listlink' type='entry2'>第二次輸入</li>
        <li class='innerlist list47 listlink' type='compare'>資料比對<hr></li>
    </div>

    @if ((int) (auth()->user()?->is_admin ?? 0) === 1)
    <div class='listinner list3inner' style='display:none;'>
        <li class='innerlist list31 listlink admin-only-link' type='process'>excel資料匯入整理</li>
        <li class='innerlist list32 listlink admin-only-link' type='import'>將資料匯入大表<hr></li>
    </div>
    @endif
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
