@extends('layouts/nanjenshan-seedling')

@section('pagejs')
    <script>
        $('.list3').addClass('now');
        $('.list3 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
        <div class='text_box'>
            <h2>南仁山小苗資料下載</h2>
            <hr>
            <table class="tablesorter" style="min-width: 620px;">
                <thead>
                    <tr>
                        <th>資料表</th>
                        <th>說明</th>
                        <th>下載</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>全部資料</td>
                        <td>資料檢視表格欄位，另加 spcode 與 survey_day。</td>
                        <td>
                            <a href="{{ route('admin.nanjenshan.seedling.download.all-data') }}">
                                <button type="button">下載 txt</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>調查樣區</td>
                        <td>quadrats 資料表。</td>
                        <td>
                            <a href="{{ route('admin.nanjenshan.seedling.download.quadrats') }}">
                                <button type="button">下載 txt</button>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>樣區植物名錄</td>
                        <td>資料建置中。</td>
                        <td>
                            <button type="button" disabled>資料建置中</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
