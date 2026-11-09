@extends('layouts/app2')

@section('title', '福山-小苗實驗-台灣森林動態樣區資料管理系統')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/seedling.css') }}?v={{ filemtime(public_path('css/seedling.css')) }}">
    <style>
        .experiment-workspace { box-sizing: border-box; width: 100%; padding: 20px; }
        .experiment-upload-intro { width: min(1000px, calc(100vw - 140px)); box-sizing: border-box; text-align: left; }
        .experiment-upload-flow { margin: 18px 0 22px; padding: 14px 18px; border: 1px solid #d9e2cf; border-radius: 5px; background: #f7faf3; box-shadow: none; }
        .experiment-upload-flow h3 { margin: 0 0 8px; font-size: 1rem; color: #365314; }
        .experiment-upload-flow ol { margin: 0; padding-left: 1.5rem; line-height: 1.8; }
        .experiment-upload-flow p { margin: 8px 0 0; color: #596451; }
        .experiment-upload-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; width: min(1000px, calc(100vw - 140px)); }
        .experiment-upload-grid .text_box { box-sizing: border-box; width: 100%; margin: 0; text-align: left; }
        .experiment-upload-grid input[type="file"] { box-sizing: border-box; width: 100%; height: auto; margin: 14px 0; padding: 8px; background: #fff; }
        .experiment-upload-meta { color: #66725d; font-size: .9rem; }
        @media (max-width: 800px) { .experiment-upload-grid { grid-template-columns: 1fr; } }
        .experiment-nav-link { color: inherit; text-decoration: none; }
        .experiment-nav-link:hover { color: inherit; text-decoration: none; }
        .experiment-viewer-tabs { display: flex; gap: 6px; margin-bottom: 18px; border-bottom: 1px solid #a9b77d; }
        .experiment-viewer-tab { width: auto; margin: 0; padding: 9px 18px; border: 1px solid transparent; border-bottom: 0; border-radius: 5px 5px 0 0; background: #eef2e3; color: #344414; box-shadow: none; cursor: pointer; }
        .experiment-viewer-tab.is-active { background: #6b8e00; color: #fff; font-weight: 700; }
        .experiment-viewer-tab:hover { background: #809d22; color: #fff; }
        .seedling-entry-page { width:min(1280px, calc(100vw - 80px)); margin:0 auto; }
        .seedling-entry-header { text-align:left; margin-bottom:20px; }
        .seedling-entry-header h2 { margin-bottom:8px; color:#344414; }
        .seedling-entry-meta { display:flex; gap:30px; flex-wrap:wrap; margin:12px 0; }
        .seedling-entry-start { width:min(760px, 100%); box-sizing:border-box; margin:0; text-align:left; }
        .seedling-entry-start form { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
        .seedling-entry-start input[type="date"] { width:170px; }
        .seedling-entry-hint { color:#66725d; }
        .seedling-entry-site-selector { margin:10px 0; text-align:left; }
        .seedling-entry-site-selector select { min-width:180px; margin-left:8px; }
        .seedling-entry-site-navigation { display:inline-flex; align-items:center; gap:20px; padding-left:20px; }
        .seedling-entry-site-navigation .a_ { cursor:pointer; }
        .seedling-entry-hidden-nav { visibility:hidden; }
        .seedling-entry-table-panel { width:100%; box-sizing:border-box; margin:16px 0 0; text-align:left; }
        .seedling-entry-table-panel h6 { margin:0; }
        .seedling-entry-revival { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin:14px 0; padding:10px 12px; border:1px solid #d9e2cf; background:#f7faf3; }
        .seedling-entry-revival select { min-width:320px; }
        .seedling-entry-revival button { width:auto; }
        .data-entry-grid-wrap { width:100%; overflow:visible; }
        .data-entry-hot { width:100%; overflow:visible; }
        .data-entry-hot .wtHolder { overflow:visible !important; }
        .data-entry-hot .ht_master .wtHolder { overflow:visible !important; }
        .data-entry-save-row { text-align:center; margin:16px 0 0; }
        .data-entry-save-row .datasavebutton { width:550px; max-width:100%; }
        .data-entry-error-cell { background:#ffd9d9 !important; color:#9b1c1c !important; }
        .seedling-entry-complete { margin:24px 0; text-align:center; }
        .seedling-entry-complete button { width:auto; }
        @media (max-width:800px) { .seedling-entry-page { width:calc(100vw - 32px); } }
    </style>
@endsection

@section('headerList')
    <div class="headerlist iflex">
        <div class="list {{ $page === 'documents' ? 'now' : '' }}">
            <a class="experiment-nav-link" href="{{ route('admin.fushan.seedling-experiment.documents') }}">相關文件<hr></a>
        </div>
        @if((int) (auth()->user()?->is_admin ?? 0) === 1)
            <div class="list {{ $page === 'upload' ? 'now' : '' }} admin-only-link">
                <a class="experiment-nav-link" href="{{ route('admin.fushan.seedling-experiment.upload') }}">上傳資料<hr></a>
            </div>
        @endif
        <div class="list list4 {{ in_array($page, ['entry-guide', 'groundcover-phase2', 'greenhouse'], true) ? 'now' : '' }}">資料輸入<hr></div>
        <div class="list {{ $page === 'data' ? 'now' : '' }}">
            <a class="experiment-nav-link" href="{{ route('admin.fushan.seedling-experiment.data') }}">資料檢視<hr></a>
        </div>
        <div class="list {{ $page === 'download' ? 'now' : '' }}">
            <a class="experiment-nav-link" href="{{ route('admin.fushan.seedling-experiment.download') }}">資料下載<hr></a>
        </div>
    </div>
@endsection

@section('headerListinner')
    <div class="listinner list4inner" style="display:none;">
        <li class="innerlist {{ $page === 'entry-guide' ? 'now' : '' }}">
            <a class="experiment-nav-link" href="{{ route('admin.fushan.seedling-experiment.entry-guide') }}">資料輸入注意事項</a>
        </li>
        <li class="innerlist {{ $page === 'groundcover-phase2' ? 'now' : '' }}">
            <a class="experiment-nav-link" href="{{ route('admin.fushan.seedling-experiment.groundcover-phase2') }}">地被實驗第二期</a>
        </li>
        <li class="innerlist {{ $page === 'greenhouse' ? 'now' : '' }}">
            <a class="experiment-nav-link" href="{{ route('admin.fushan.seedling-experiment.greenhouse') }}">溫室實驗<hr></a>
        </li>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const parent = document.querySelector('.list4');
            const submenu = document.querySelector('.list4inner');
            if (!parent || !submenu) return;
            let timer;
            const show = () => { clearTimeout(timer); submenu.style.display = 'inline-flex'; };
            const hide = () => { timer = setTimeout(() => submenu.style.display = 'none', 120); };
            parent.addEventListener('mouseenter', show);
            parent.addEventListener('mouseleave', hide);
            submenu.addEventListener('mouseenter', show);
            submenu.addEventListener('mouseleave', hide);
            parent.addEventListener('click', show);
        });
    </script>
    @if ($page === 'groundcover-phase2')
        <script src="{{ asset('/js/data-entry-grid.js') }}?v={{ filemtime(public_path('js/data-entry-grid.js')) }}"></script>
    @endif
@endsection

@section('content')
    <div class="icon icon2"><img src="{{ asset('/images/黃杞_苗_72_250.png') }}" alt="小苗圖案"></div>
    @include('includes.header2')
    <div class="content">
        <main class="experiment-workspace">@yield('workspace')</main>
    </div>
@endsection
