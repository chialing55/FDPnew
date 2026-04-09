@extends('layouts/nanjenshan-seedling')

@section('pagejs')
    <script>
        $('.list1').addClass('now');
        $('.list1 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='flex text_outbox'>
        <div class='text_box'>
            <h2>南仁山小苗相關文件</h2>
            <hr>
            <ol>
                <li>這裡可補上南仁山小苗調查文件與操作說明。</li>
                <li>可加入樣區分布圖、調查規則與紀錄格式。</li>
                <li>若之後需要，可再補輸入頁與資料比對頁。</li>
            </ol>
        </div>

        <div class='text_box'>
            <h2>工作流程</h2>
            <hr>
            <ol>
                <li>先整理南仁山小苗相關文件。</li>
                <li>再到<a href="{{ route('admin.nanjenshan.seedling.dataviewer') }}">資料檢視</a>查看資料。</li>
            </ol>
        </div>
    </div>
@endsection
