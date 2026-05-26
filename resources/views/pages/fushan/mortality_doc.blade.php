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
            <p>內容待補。</p>
        </div>

        <div class='text_box'>
            <h2>工作流程</h2>
            <hr>
            <ol>
                <li>先在<a href="{{ route('admin.fushan.mortality.census') }}">調查年度</a>確認本次調查與年份。</li>
                <li>到<a href="{{ route('admin.fushan.mortality.survey-import') }}">匯入調查資料</a>上傳或確認本次調查資料。</li>
                <li>資料輸入前，先到<a href="{{ route('admin.fushan.mortality.entry.1') }}">第一次輸入</a>建立輸入表單。</li>
                <li>正式輸入前，先閱讀<a href="{{ route('admin.fushan.mortality.note') }}">輸入注意事項</a>。</li>
                <li>完成<a href="{{ route('admin.fushan.mortality.entry.1') }}">第一次輸入</a>與<a href="{{ route('admin.fushan.mortality.entry.2') }}">第二次輸入</a>。</li>
                <li>完成兩次輸入後，到<a href="{{ route('admin.fushan.mortality.compare') }}">資料比對</a>確認差異。</li>
                @if((int) (auth()->user()?->is_admin ?? 0) === 1)
                    <li>確認無誤後，再到<a class="admin-only-body-link" href="{{ route('admin.fushan.mortality.import') }}">將資料匯入大表</a>。</li>
                    <li>如需進一步整理資料，可到<a class="admin-only-body-link" href="{{ route('admin.fushan.mortality.process') }}">資料處理</a>頁面。</li>
                @endif
            </ol>
        </div>
    </div>
@endsection
