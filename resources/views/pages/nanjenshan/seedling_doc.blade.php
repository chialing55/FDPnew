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
            <h2>相關文件</h2>
            <hr>
            <ol>
                <li><a href="https://www.notion.so/34aed0b14d7e80dfba52d5f768f6a7dd?source=copy_link" target="_blank">南仁山小苗資料表說明</a></li>

            </ol>
        </div>

        {{-- <div class='text_box'>
            <h2>工作流程</h2>
            <hr>
            <ol>
                <li>先整理南仁山小苗相關文件。</li>
                <li>再到<a href="{{ route('admin.nanjenshan.seedling.dataviewer') }}">資料檢視</a>查看資料。</li>
            </ol>
        </div> --}}
    </div>
@endsection
