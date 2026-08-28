@extends('layouts/app2')

@section('title', $sitec . '-' . $project . '
-台灣森林動態樣區資料管理系統')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/fstree.css') }}">
    <style>
        .plant-catalog-icon-placeholder {
            width: 72px;
            height: 72px;
        }
    </style>
@endsection

@section('js')
    <script>
        $('.listlink').on('click', function() {
            let type = $(this).attr('type');
            if (typeof type !== 'undefined') {
                location.href = `/admin/plant-catalog/${type}`;
            }
        });
    </script>

    @yield('pagejs')
@endsection

@section('headerList')
    @php($isAdmin = (int) (auth()->user()?->is_admin ?? 0) === 1)
    <div class='headerlist iflex'>
        @if ($isAdmin)
            <div class='list list1 listlink admin-only-link' type='upload'>名錄上傳<hr></div>
            <div class='list list4 listlink admin-only-link' type='maintenance'>名錄整理<hr></div>
        @endif
        <div class='list list2 listlink' type='download'>名錄下載<hr></div>
        <div class='list list3 listlink' type='photos'>照片編輯<hr></div>
    </div>
@endsection

@section('content')
    <div class="icon">
        <div class="plant-catalog-icon-placeholder" aria-hidden="true"></div>
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
