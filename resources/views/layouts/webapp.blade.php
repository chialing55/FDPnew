@php
    date_default_timezone_set('Asia/Taipei');
@endphp
<!DOCTYPE html>
<html lang="tw">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="language" content="zh-TW" />
    <link rel="alternate" href="" hreflang="en" />
    <title>@yield('title')</title>
    @yield('meta')

    <link rel="stylesheet" href="{{ asset('/js/handsontable/dist/handsontable.full.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/webstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/webstr.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/fancybox.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/theme.green.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/tailwind.css') }}">
    <link href="{{ asset('/css/all.min.css') }}" rel="stylesheet"> {{-- Font Awesome --}}
    @yield('css')

    @yield('header_js')
</head>

<body class="text-gray-900 min-h-screen flex flex-col">

    @include('includes.webheader')
    <div>
        @yield('hero')
    </div>
    <main class="p-6 flex-1">
        <div class='m-auto max-w-7xl w-full'>
            @yield('content')
        </div>
    </main>
    <footer>
        <div class='bg-forest-dark p-2 text-white flex justify-between' style='font-size: 14px;'>
            <p>
                國立東華大學 NDHU / 國立中山大學 NSYSU / 國立台灣大學 NTU<br>
                林務局 TFB / 林業試驗所 TFRI / ForestGEO
            </p>
            @if (session('latest_update'))
                <p style='margin-left: 100px;'>更新日期：{{ session('latest_update') }}</p>
            @endif
        </div>
    </footer>

</body>

{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --}}
<script src="{{ asset('/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('/js/handsontable/dist/handsontable.full.js') }}"></script>
<script src="{{ asset('/js/jquery.tablesorter.min.js') }}"></script>
<script src="{{ asset('/js/jquery.tablesorter.widgets.min.js') }}"></script>
<script src="{{ asset('/js/fancybox.umd.js') }}"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

@livewireScripts



@yield('js')

@php

@endphp

</html>
