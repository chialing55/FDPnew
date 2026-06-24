@extends('layouts/seedling')

@section('pagejs')
<script>
$(function () {
  $('.list5').addClass('now');

  const hasAppliedRange = @json($hasAppliedRange);
  const startCensus = @json($selectedStartCensus);
  const endCensus = @json($selectedEndCensus);
  const itemUrls = @json($itemUrls ?? []);
  const clearSessionUrl = @json($clearSessionUrl ?? null);
  const csrfToken = @json(csrf_token());
  let skipNextSessionClear = false;

  if (!hasAppliedRange) {
    return;
  }

  $('.js-research-output-detail').on('toggle', function() {
    const detail = this;
    if (!detail.open || detail.dataset.loaded === '1' || detail.dataset.loading === '1') {
      return;
    }

    const item = detail.dataset.item;
    const url = itemUrls[item];
    const body = detail.querySelector('.js-research-output-body');

    if (!url || !body) {
      return;
    }

    detail.dataset.loading = '1';
    body.innerHTML = '<p style="margin:0;">資料整理中...</p>';

    const params = new URLSearchParams({
      start_census: startCensus,
      end_census: endCensus,
    });

    fetch(url + '?' + params.toString(), {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
      .then(function(response) {
        return response.json().then(function(data) {
          if (!response.ok) {
            throw new Error(data.error || '載入失敗');
          }
          return data;
        });
      })
      .then(function(data) {
        body.innerHTML = data.html || '<p style="margin:0;">此項目尚無內容。</p>';
        detail.dataset.loaded = '1';
      })
      .catch(function(error) {
        body.innerHTML = '<p style="margin:0; color:#9b1c1c;">' + error.message + '</p>';
      })
      .finally(function() {
        detail.dataset.loading = '0';
      });
  });

  document.addEventListener('click', function(event) {
    const link = event.target.closest('a[href*="/research-output/asset/"]');

    if (!link) {
      return;
    }

    skipNextSessionClear = true;
    window.setTimeout(function() {
      skipNextSessionClear = false;
    }, 3000);
  });

  window.addEventListener('pagehide', function() {
    if (!clearSessionUrl || skipNextSessionClear) {
      return;
    }

    const payload = new FormData();
    payload.append('_token', csrfToken);
    navigator.sendBeacon(clearSessionUrl, payload);
  });
});
</script>
@endsection

@section('rightbox')
@php
    $outputItems = [
        ['key' => 'composition', 'label' => '喬木小苗之植物組成'],
        ['key' => 'survival-growth', 'label' => '喬木小苗之存活與生長'],
    ];
@endphp

<div class='flex text_outbox'>
    <div style='width: min(960px, 100%);'>
        <form method='GET' action='{{ url()->current() }}' style='display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:12px 0 18px;'>
            <strong>資料範圍</strong>

            <label for='seedling-output-start-census'>從</label>
            <select id='seedling-output-start-census' name='start_census' class='fs100' style='width:auto; min-width:210px;'>
                @forelse ($dateOptions as $option)
                    <option value='{{ $option['census'] }}' @selected((string) $option['census'] === (string) $selectedStartCensus)>{{ $option['label'] }}</option>
                @empty
                    <option value=''>尚無 seedling_records 資料</option>
                @endforelse
            </select>

            <label for='seedling-output-end-census'>到</label>
            <select id='seedling-output-end-census' name='end_census' class='fs100' style='width:auto; min-width:210px;'>
                @forelse ($dateOptions as $option)
                    <option value='{{ $option['census'] }}' @selected((string) $option['census'] === (string) $selectedEndCensus)>{{ $option['label'] }}</option>
                @empty
                    <option value=''>尚無 seedling_records 資料</option>
                @endforelse
            </select>

            <button type='submit' class='datasavebutton' style='width:auto;'>套用</button>
        </form>

        @if (! $hasAppliedRange)
            <p style='margin:0;'>請先選擇資料範圍並按「套用」，再開啟各項計畫成果。</p>
        @else
            <div style='display:flex; flex-direction:column; gap:10px;'>
                @foreach ($outputItems as $item)
                    <details class='js-research-output-detail' data-item='{{ $item['key'] }}' style='border:1px solid #d8dec0; border-radius:6px; background:#fff; padding:10px 12px;'>
                        <summary style='cursor:pointer; font-weight:700;'>{{ $loop->iteration }}. {{ $item['label'] }}</summary>
                        <div class='js-research-output-body' style='padding:12px 0 2px 20px; line-height:1.8;'>
                            <p style='margin:0;'>點開後載入資料。</p>
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
