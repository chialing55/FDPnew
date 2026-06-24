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
                    <td>全部資料</td>
                    <td>
                        <form id="seedling-download-form" method="GET" action="{{ route('admin.fushan.seedling.download.all-data') }}" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin:0;">
                            <label for="seedling-download-start-census">從</label>
                            <select id="seedling-download-start-census" name="start_census" class="fs100" style="width:auto; min-width:190px;">
                                @forelse ($dateOptions as $option)
                                    <option value="{{ $option['census'] }}" @selected((string) $option['census'] === (string) $selectedStartCensus)>{{ $option['label'] }}</option>
                                @empty
                                    <option value="">尚無小苗調查資料</option>
                                @endforelse
                            </select>

                            <label for="seedling-download-end-census">到</label>
                            <select id="seedling-download-end-census" name="end_census" class="fs100" style="width:auto; min-width:190px;">
                                @forelse ($dateOptions as $option)
                                    <option value="{{ $option['census'] }}" @selected((string) $option['census'] === (string) $selectedEndCensus)>{{ $option['label'] }}</option>
                                @empty
                                    <option value="">尚無小苗調查資料</option>
                                @endforelse
                            </select>
                        </form>
                    </td>
                    <td>依 census 範圍下載小苗資料（seedling_records、seedling_stems、seedling_individuals 合併欄位）。</td>
                    <td>
                        @if ($dateOptions->isNotEmpty())
                            <button type="submit" form="seedling-download-form">下載 txt</button>
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
