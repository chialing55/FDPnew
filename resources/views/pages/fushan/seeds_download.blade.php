@extends('layouts/seeds')

@section('pagejs')
<script type="text/javascript">
  $('.list8').addClass('now');
  $('.list8 hr').css('color', '#91A21C');
</script>
@endsection

@section('rightbox')
<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class='text_box'>
        <h2>種子資料下載</h2>
        <hr>
        <table class="tablesorter" style="min-width: 760px;">
            <thead>
                <tr>
                    <th>資料表</th>
                    <th>資料範圍</th>
                    <th>說明</th>
                    <th>下載</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>fulldata</td>
                    <td>
                        <form id="seeds-download-form" method="GET" action="{{ route('admin.fushan.seeds.download.fulldata') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:0;">
                            <label for="seeds-download-start-census">從</label>
                            <select id="seeds-download-start-census" name="start_census" class="fs100" style="width:auto; min-width:190px;">
                                @forelse ($dateOptions as $option)
                                    <option value="{{ $option['census'] }}" @selected((string) $option['census'] === (string) $selectedStartCensus)>{{ $option['label'] }}</option>
                                @empty
                                    <option value="">尚無 dateinfo 資料</option>
                                @endforelse
                            </select>

                            <label for="seeds-download-end-census">到</label>
                            <select id="seeds-download-end-census" name="end_census" class="fs100" style="width:auto; min-width:190px;">
                                @forelse ($dateOptions as $option)
                                    <option value="{{ $option['census'] }}" @selected((string) $option['census'] === (string) $selectedEndCensus)>{{ $option['label'] }}</option>
                                @empty
                                    <option value="">尚無 dateinfo 資料</option>
                                @endforelse
                            </select>
                        </form>
                    </td>
                    <td>fulldata 依 census 範圍下載，並接 dateinfo 的 date、date1、year、month、period。</td>
                    <td>
                        @if ($dateOptions->isNotEmpty())
                            <button type="submit" form="seeds-download-form">下載 txt</button>
                        @else
                            <button type="button" disabled>尚無資料</button>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
