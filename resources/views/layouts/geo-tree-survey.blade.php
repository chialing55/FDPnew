@extends('layouts/app2')

@php($sitec = $site === 'fushan' ? '福山' : $site)

@section('title', $sitec . '-' . $project . '-台灣森林動態樣區資料管理系統')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/seedling.css') }}?v={{ filemtime(public_path('css/seedling.css')) }}">
    <link rel="stylesheet" href="{{ asset('/css/fstree.css') }}?v={{ filemtime(public_path('css/fstree.css')) }}">
    <link rel="stylesheet" href="{{ asset('/css/tree-entry.css') }}?v={{ filemtime(public_path('css/tree-entry.css')) }}">
@endsection

@section('js')
    <script src="{{ asset('/js/tree-entry-grid.js') }}?v={{ filemtime(public_path('js/tree-entry-grid.js')) }}"></script>
    <script>
        $('.listlink').on('click', function () {
            const type = $(this).attr('type');
            if (type) {
                location.href = `/admin/fushan/geo-tree-survey/${type}`;
            }
        });

        handleHoverEvents('.list4', '.list4inner');
    </script>
    @yield('pagejs')
@endsection

@section('headerList')
    <div class="headerlist iflex">
        <div class="list list1 listlink" type="doc">相關文件<hr></div>
        <div class="list list4">資料輸入<hr></div>
        <div class="list list2 listlink" type="dataviewer">資料檢視<hr></div>
        <div class="list list5 listlink" type="download">資料下載<hr></div>
    </div>
@endsection

@section('headerListinner')
    <div class="listinner list4inner" style="display:none;">
        <li class="innerlist list41 listlink" type="note">資料輸入注意事項</li>
        <li class="innerlist list42 listlink" type="entry1">第一次輸入</li>
        <li class="innerlist list43 listlink" type="entry2">第二次輸入</li>
        <li class="innerlist list44 listlink" type="compare">資料比對<hr></li>
    </div>
@endsection

@section('content')
    <div class="icon icon2"></div>
    @include('includes.header2')

    <div class="content">
        <div class="right">
            <div style="display: grid; justify-items: center;">
                @yield('rightbox')
            </div>
        </div>
    </div>
@endsection
