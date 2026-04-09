@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list1').addClass('now');
        $('.list1 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='flex text_outbox'>
        <div class='text_box'>
            <h2>死亡率調查相關文件</h2>
            <hr>
            <ol>
                <li><a href="{{ route('admin.fushan.mortality.entry.1') }}">第一次輸入</a></li>
                <li><a href="{{ route('admin.fushan.mortality.entry.2') }}">第二次輸入</a></li>
                <li><a href="{{ route('admin.fushan.mortality.record') }}">調查紀錄</a></li>
            </ol>
        </div>

        <div class='text_box'>
            <h2>工作流程</h2>
            <hr>
            <ol>
                <li>先整理死亡率調查原始紀錄與工作規則。</li>
                <li>完成兩次輸入後，到<a href="{{ route('admin.fushan.mortality.compare') }}">資料比對</a>確認差異。</li>
                <li>確認無誤後，再到<a href="{{ route('admin.fushan.mortality.import') }}">將資料匯入大表</a>。</li>
            </ol>
        </div>

        <div class='text_box'>
            <h2>後續待補</h2>
            <hr>
            <p>這一套頁面目前先完成骨架。接下來可以依死亡率調查的實際欄位與流程，把表格、驗證、匯入與查詢邏輯逐步補上。</p>
        </div>
    </div>
@endsection
