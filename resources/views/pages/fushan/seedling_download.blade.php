@extends('layouts/seedling')

@section('pagejs')
<script type="text/javascript">
  $('.list3').addClass('now');
  $('.list3 hr').css('color', '#91A21C');
</script>
@endsection

@section('rightbox')
<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class='text_box'>
        <h2>小苗資料下載</h2>
        <hr>
        <table class="tablesorter" style="min-width: 520px;">
            <thead>
                <tr>
                    <th>資料表</th>
                    <th>說明</th>
                    <th>下載</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>seedling</td>
                    <td>目前最新的完整小苗分析資料：{{ $latestSeedlingYm }}。</td>
                    <td>
                        <a href="{{ route('admin.fushan.seedling.download.seedling') }}">
                            <button type="button">下載 txt</button>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
