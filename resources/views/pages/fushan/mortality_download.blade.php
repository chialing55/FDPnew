@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list5').addClass('now');
        $('.list5 hr').css('color', '#91A21C');
    </script>
@endsection

@section('rightbox')
    <div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
        <div class='text_box'>
            <h2>死亡率調查資料下載</h2>
            <hr>
            <p style="margin: 10px 0 18px;">資料下載頁面已建立，下載項目之後再補。</p>
            @if ((int) (auth()->user()?->is_admin ?? 0) === 1)
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
                        <td>census_records</td>
                        <td>最新 census {{ $latestCensusText }} 的死亡率調查資料。</td>
                        <td>
                            @if ($latestCensus)
                                <a href="{{ route('admin.fushan.mortality.download.latest-census-records') }}">
                                    <button type="button">下載 txt</button>
                                </a>
                            @else
                                <button type="button" disabled>尚無資料</button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            @endif
        </div>
    </div>
@endsection
