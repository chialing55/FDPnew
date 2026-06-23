@if($item === 'composition')
  @if(count($compositionSummary['surveys'] ?? []) === 0)
    <p style='margin:0;'>此資料範圍內尚無可彙整的小苗調查資料。</p>
  @else
    <style>
      .seedling-output-table {
        border-collapse: collapse;
        margin: 12px 0 22px;
        width: min(900px, 100%);
        background: #fff;
      }
      .seedling-output-table th,
      .seedling-output-table td {
        border-bottom: 1px solid #222;
        padding: 4px 8px;
        text-align: left;
        vertical-align: top;
      }
      .seedling-output-table thead th {
        font-weight: 400;
      }
      .seedling-output-table .num {
        text-align: right;
        white-space: nowrap;
      }
      .seedling-output-table tfoot td {
        border-top: 1px solid #222;
        border-bottom: 1px solid #222;
      }
    </style>

    @if(!empty($compositionAssets['docx_url']))
      <div style='margin-bottom:12px;'>
        <a href='{{ $compositionAssets['docx_url'] }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 Word 表格</a>
      </div>
    @endif
    @if(!empty($compositionAssets['error']))
      <p style='margin:0 0 10px; color:#9b1c1c;'>圖檔產生失敗：{{ $compositionAssets['error'] }}</p>
    @endif

    @foreach($compositionSummary['surveys'] as $survey)
      @php
        $topAlive = $survey['top_alive'];
        $topNewFive = $survey['top_new_five'];
        $topDeadFive = $survey['top_dead_five'];
        $topAliveText = collect($topAlive)->reject(fn($row) => $row['csp'] === '其他物種')->take(6)->map(fn($row) => $row['csp'] . '(' . number_format($row['count']) . '棵)')->implode('、');
        $topNewText = collect($topNewFive)->map(fn($row) => $row['csp'] . '(' . number_format($row['count']) . '棵)')->implode('、');
        $topDeadText = collect($topDeadFive)->map(fn($row) => $row['csp'] . '(' . number_format($row['count']) . '株)')->implode('、');
        $newSeedlingPercent =($survey['alive_total'] ?? 0) > 0 ?($survey['new_total'] / $survey['alive_total'] * 100) : 0;
        $newSeedlingSentence = '本次所記錄的喬木小苗中，有' . number_format($survey['new_total']) . '棵是本次調查新增之幼苗(表3)，佔總株數的' . number_format($newSeedlingPercent, 1) . '%，相較於往年是新增小苗量較少的年度。此次調查中新增小苗數量最多的5個樹種分別是' .($topNewText !== '' ? $topNewText : '無新增小苗') . '，然其新增小苗數量之排名與所有存活小苗個體數排名不盡相同。';
        $change = $survey['species_change'];
        $changeSpecies = $change['delta'] < 0 ? $change['lost'] : $change['gained'];
        $changeSpeciesText = count($changeSpecies) > 0 ? '(' . implode('、', $changeSpecies) . ') ' : '';
        $rowCount = max(count($survey['table']['alive']), count($survey['table']['new']), count($survey['table']['dead']));
        if($survey['sequence'] === 1) {
          $surveySentence = '我們於 ' . $survey['survey_date_text'] . ' 進行計劃期間第' . $number($survey['sequence']) . '次小苗調查，包括存活舊苗與新增苗在內，總計調查到' . $number($survey['family_count']) . '科' . $number($survey['genus_count']) . '屬' . $number($survey['species_count']) . '種共' . $number($survey['alive_total']) . '株小苗，平均密度為' . $decimal($survey['density']) . '棵/m2。其中以' . $topAliveText . '之個體數最多。在' . $number($survey['species_count']) . '種記錄到的喬木小苗中，個體數最多的前10名植物佔了總株數的' . $percent($survey['top_alive_percent']) . '%，其餘物種之小苗數量均相對稀少。';
        } else {
          $surveySentence = '第' . $number($survey['sequence']) . '次小苗調查於 ' . $survey['survey_date_text'] . ' 進行，包括存活舊苗與新增苗在內，總計調查到' . $number($survey['alive_total']) . '株小苗';
          if($change['has_previous']) {
            $surveySentence .= '，較前一次調查' .($change['delta'] > 0 ? '增加' :($change['delta'] < 0 ? '減少' : '沒有增減')) . '了' . $number(abs($change['delta'])) . '種' . $changeSpeciesText;
          }
          $surveySentence .= '，平均密度為' . $decimal($survey['density']) . '棵/m2。死亡小苗中株數最高的前5個樹種為' .($topDeadText !== '' ? $topDeadText : '無死亡小苗') . '。';
        }
      @endphp

      <p style='margin:0 0 10px;'>{{ $surveySentence }}</p>
      <p style='margin:0 0 10px;'>{{ $newSeedlingSentence }}</p>

      <div style='margin-top:16px;'>
        <p style='margin:0 0 6px;'>
          @if($survey['previous_month_text'])
            福山森林動態樣區 {{ $survey['previous_month_text'] }} 至 {{ $survey['survey_month_text'] }} 喬木小苗之動態變化
          @else
            福山森林動態樣區 {{ $survey['survey_month_text'] }} 喬木小苗之植物組成
          @endif
        </p>
        @if($survey['previous_month_text'])
          <p style='margin:0 0 6px;'>其中存活小苗數量包含{{ $survey['previous_month_text'] }}調查存活之舊苗與{{ $survey['survey_month_text'] }}調查新增之小苗。</p>
        @endif

        <table class='seedling-output-table'>
          <thead>
            <tr>
              <th colspan='2'>存活小苗數量</th>
              <th colspan='2'>新增小苗數量</th>
              <th colspan='2'>死亡小苗數量</th>
            </tr>
            <tr>
              <th>物種</th>
              <th class='num'>株數</th>
              <th>物種</th>
              <th class='num'>株數</th>
              <th>物種</th>
              <th class='num'>株數</th>
            </tr>
          </thead>
          <tbody>
            @for($i = 0; $i < $rowCount; $i++)
              @php
                $alive = $survey['table']['alive'][$i] ?? null;
                $new = $survey['table']['new'][$i] ?? null;
                $dead = $survey['table']['dead'][$i] ?? null;
              @endphp
              <tr>
                <td>{{ $alive['csp'] ?? '' }}</td>
                <td class='num'>{{ isset($alive) ? number_format($alive['count']) : '' }}</td>
                <td>{{ $new['csp'] ?? '' }}</td>
                <td class='num'>{{ isset($new) ? number_format($new['count']) : '' }}</td>
                <td>{{ $dead['csp'] ?? '' }}</td>
                <td class='num'>{{ isset($dead) ? number_format($dead['count']) : '' }}</td>
              </tr>
            @endfor
          </tbody>
          <tfoot>
            <tr>
              <td>小苗總數</td>
              <td class='num'>{{ number_format($survey['table']['alive_total']) }}</td>
              <td></td>
              <td class='num'>{{ number_format($survey['table']['new_total']) }}</td>
              <td></td>
              <td class='num'>{{ number_format($survey['table']['dead_total']) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      @php
        $figure = $compositionAssets['figures'][$survey['census']] ?? null;
      @endphp
      @if($figure)
        <div style='margin:18px 0 22px;'>
          <p style='margin:0 0 10px;'>
            圖. 福山森林動態樣區 {{ $survey['survey_month_text'] }} 各喬木植物之小苗數量。圖中白色為存活舊苗、淺灰色為新增苗、斜線為死亡苗，其中大明橘因存活舊苗與死亡苗數量遠高於其他物種不全部顯示。
          </p>
          <div style='margin-bottom:10px;'>
            <a href='{{ $figure['pdf_url'] }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 PDF</a>
          </div>
          <img src='{{ $figure['png_url'] }}' alt='喬木小苗之植物組成圖' style='width:15cm; max-width:100%; height:auto; border:1px solid #ddd; background:#fff;'>
        </div>
      @endif
    @endforeach
  @endif
@elseif($item === 'survival-growth')
  @php
    $intervals = $survivalGrowthSummary['intervals'] ?? [];
  @endphp
  <style>
    .seedling-survival-table { border-collapse: collapse; margin: 12px 0 22px; width: min(760px, 100%); background:#fff; }
    .seedling-survival-table th, .seedling-survival-table td { border-bottom: 1px solid #222; padding: 4px 8px; text-align:left; vertical-align:top; }
    .seedling-survival-table thead th { font-weight:400; border-top:1px solid #222; }
    .seedling-survival-table .num { text-align:right; white-space:nowrap; }
  </style>
  @if(count($intervals) === 0)
    <p style='margin:0;'>此資料範圍內尚無可與前一次調查比較的小苗存活與生長資料。</p>
  @else
    @if(!empty($survivalGrowthDocxUrl))
      <div style='margin-bottom:12px;'>
        <a href='{{ $survivalGrowthDocxUrl }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 Word 表格</a>
      </div>
    @endif
    <p style='margin:0 0 10px;'>
      我們篩選於前一次小苗調查中存活植株數量 ≥10株的樹種，分別計算各樹種之年存活率與年生長率。
    </p>
    <p style='margin:0 0 10px;'>
      年存活率 s 計算公式為 s =( S / N ) ^( 1 / t )，其中 N 為前一次調查存活的小苗個體數，S 則為前一次調查的小苗在本次調查存活下來的小苗個體數，t 則為兩次調查的時間間隔，單位為年。少數小苗會因枝條斷折導致高度大幅減少，若使用平均值計算各樹種之生長率會受到少數大量負生長影響，因此本報告選用中位數統計各樹種之年生長率。
    </p>

    @foreach($intervals as $interval)
      @php
        $topNames = collect($interval['top_survival'])->pluck('csp')->implode('、');
        $topRate = $interval['top_survival'][0]['survival_rate'] ?? null;
        $nextText = collect($interval['next_survival'])->map(fn($row) => $row['csp'] . '，為 ' . number_format($row['survival_rate'], 3))->implode('；');
        $topRecruit = $interval['top_recruit'];
        $topGrowthText = collect($interval['top_growth'])->map(fn($row) => $row['csp'] . '(' . number_format($row['growth_cm_rate'], 3) . ' cm/year)')->implode('、');
        $sentence = '第' . $number($interval['previous_census']) . '次(' . $interval['previous_month_text'] . ') 至第' . $number($interval['current_census']) . '次(' . $interval['current_month_text'] . ') 調查，存活率最高的樹種為' . $topNames;
        if($topRate !== null) {
          $sentence .= '，年存活率為' . number_format($topRate, 3);
        }
        $sentence .= '。';
        if($nextText !== '') {
          $sentence .= '其次為' . $nextText . '。';
        }
        if($topRecruit) {
          $sentence .= '前一次調查中新增苗數量最多的' . $topRecruit['csp'] . '，其年存活率為' . number_format($topRecruit['survival_rate'], 3) . '。';
        }
        if($interval['range_min'] !== null && $interval['range_max'] !== null) {
          $sentence .= '其餘樹種之年存活率則介於' . number_format($interval['range_min'], 3) . '–' . number_format($interval['range_max'], 3) . '之間。';
        }
        if($topGrowthText !== '') {
          $sentence .= '年生長率中位數較高的樹種為' . $topGrowthText . '。';
        }
      @endphp
      <p style='margin:0 0 10px;'>{{ $sentence }}</p>
      <p style='margin:0 0 6px;'>
        表 福山森林動態樣區喬木樹種小苗之存活率及生長率
      </p>
      <p style='margin:0 0 6px;'>
        挑選{{ $interval['previous_month_text'] }}之調查中小苗數量 ≥10株的樹種進行計算，N 為{{ $interval['previous_month_text'] }}所調查到之小苗數量，S 為{{ $interval['current_month_text'] }}存活之舊苗數量。
      </p>
      <table class='seedling-survival-table'>
        <thead>
          <tr>
            <th>樹種</th>
            <th class='num'>N</th>
            <th class='num'>S</th>
            <th class='num'>年存活率</th>
            <th class='num'>年生長率<br>(cm/year)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($interval['rows'] as $row)
            <tr>
              <td>{{ $row['csp'] }}</td>
              <td class='num'>{{ number_format($row['previous_alive_total']) }}</td>
              <td class='num'>{{ number_format($row['survived_total']) }}</td>
              <td class='num'>{{ number_format($row['survival_rate'], 3) }}</td>
              <td class='num'>{{ $row['growth_cm_rate'] === null ? '' : number_format($row['growth_cm_rate'], 3) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endforeach

    @if(!empty($growthHistogramAssets['error']))
      <p style='margin:0 0 10px; color:#9b1c1c;'>圖檔產生失敗：{{ $growthHistogramAssets['error'] }}</p>
    @endif
    @foreach(($growthHistogramAssets['figures'] ?? []) as $figure)
      @if(!empty($figure['png_url']))
        <div style='margin:18px 0 22px;'>
          <p style='margin:0 0 10px;'>
            圖. 福山森林動態樣區喬木小苗於{{ $figure['current_month_text'] }}生長率之頻度分布圖。圖中橫軸為年生長率，單位為 cm/year，縱軸則為小苗之個體數。
          </p>
          <div style='margin-bottom:10px;'>
            <a href='{{ $figure['pdf_url'] }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 PDF</a>
          </div>
          <img src='{{ $figure['png_url'] }}' alt='喬木小苗生長率頻度分布圖' style='width:15cm; max-width:100%; height:auto; border:1px solid #ddd; background:#fff;'>
        </div>
      @endif
    @endforeach
  @endif
@else
  <p style='margin:0;'>{{ $placeholder ?? '成果內容建置中。' }}</p>
@endif
