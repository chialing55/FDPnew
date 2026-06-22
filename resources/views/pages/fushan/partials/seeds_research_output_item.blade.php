@if ($item === 'composition')
  @if (($compositionSummary['survey_count'] ?? 0) === 0)
    <p style='margin:0;'>此資料範圍內尚無可彙整的種子雨調查資料。</p>
  @else
    <p style='margin:0 0 10px;'>
      自 {{ $compositionSummary['start_date_text'] }} 至 {{ $compositionSummary['end_date_text'] }} 止，為期{{ $number($compositionSummary['survey_count']) }}周共{{ $number($compositionSummary['survey_count']) }}次的種子雨調查中，總計收集到{{ $number($compositionSummary['family_count']) }}科{{ $number($compositionSummary['genus_count']) }}屬{{ $number($compositionSummary['species_count']) }}種植物的花、果實或種子。調查期間共記錄到開花{{ $number($compositionSummary['flower_record_count']) }}筆，以及成熟果實{{ $number($compositionSummary['mature_fruit_count']) }}顆、成熟種子{{ $number($compositionSummary['mature_seed_count']) }}顆；若包含成熟果實內之種子則共計{{ $number($compositionSummary['seed_total_with_fruits']) }}顆種子，另有{{ $number($compositionSummary['small_fruit_count']) }}顆成熟果實因其種子小於收集網之網目，所以不計算其種子數量。
    </p>

    <p style='margin:0 0 10px;'>
      在這{{ $number($compositionSummary['survey_count']) }}周的種子雨調查中，
      @if (count($compositionSummary['flower_top']) > 0)
        以{{ $compositionSummary['flower_top'][0]['csp'] }}的落花紀錄最多，共有{{ $number($compositionSummary['flower_top'][0]['total']) }}筆@if (isset($compositionSummary['flower_top'][1]))，其次是{{ $compositionSummary['flower_top'][1]['csp'] }}，共有{{ $number($compositionSummary['flower_top'][1]['total']) }}筆@endif@if (isset($compositionSummary['flower_top'][2]))，{{ $compositionSummary['flower_top'][2]['csp'] }}則有{{ $number($compositionSummary['flower_top'][2]['total']) }}筆@endif (圖a) 。
      @else
        尚無開花紀錄。
      @endif
      @if (count($compositionSummary['fruit_top']) > 0)
        所收集的成熟果實中，以{{ $compositionSummary['fruit_top'][0]['csp'] }}的果實數量最多，共有{{ $number($compositionSummary['fruit_top'][0]['total']) }}顆@if (isset($compositionSummary['fruit_top'][1]))，其次是{{ $compositionSummary['fruit_top'][1]['csp'] }}，共有{{ $number($compositionSummary['fruit_top'][1]['total']) }}顆@endif@if (isset($compositionSummary['fruit_top'][1]))，這兩種植物果實數占總果實數之{{ $percent($compositionSummary['fruit_top_two_percent']) }}%@endif (圖b) 。
      @else
        尚無成熟果實紀錄。
      @endif
      @if (count($compositionSummary['seed_top']) > 0)
        所收集的種子中，以{{ $compositionSummary['seed_top'][0]['csp'] }}的種子數量最多，共有{{ $number($compositionSummary['seed_top'][0]['total']) }}顆@if (isset($compositionSummary['seed_top'][1]))，其次是{{ $compositionSummary['seed_top'][1]['csp'] }}，共有{{ $number($compositionSummary['seed_top'][1]['total']) }}顆@endif@if (isset($compositionSummary['seed_top'][1]))，這兩種植物種子數占總種子數之{{ $percent($compositionSummary['seed_top_two_percent']) }}%@endif (圖c) 。
      @else
        尚無成熟種子紀錄。
      @endif
    </p>

    @if (count($compositionSummary['seed_only_species']) > 0 || count($compositionSummary['fragment_only_species']) > 0)
      <p style='margin:0;'>
        @if (count($compositionSummary['seed_only_species']) > 0)
          此外，{{ implode('、', $compositionSummary['seed_only_species']) }} 在這段期間僅收集到種子，並無完整果實。
        @endif
        @if (count($compositionSummary['fragment_only_species']) > 0)
          另有{{ implode('、', $compositionSummary['fragment_only_species']) }}只收集到果實碎片，並無完整果實或種子。
        @endif
      </p>
    @endif

    <div style='margin-top:18px;'>
      <p style='margin:0 0 10px;'>
        圖. 福山森林動態樣區 {{ $compositionSummary['start_date_text'] }} 至 {{ $compositionSummary['end_date_text'] }} 種子雨之主要植物組成。 (a) 為各物種所收集到之落花紀錄筆數； (b) 為成熟果實之數量，橫軸為對數刻度； (c) 則為種子之數量。
      </p>
      @if (!empty($compositionFigure['png_url']))
        <div style='margin-bottom:10px;'>
          <a href='{{ $compositionFigure['pdf_url'] }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 PDF</a>
        </div>
        <img src='{{ $compositionFigure['png_url'] }}' alt='種子雨之植物組成圖' style='width:15cm; max-width:100%; height:auto; border:1px solid #ddd; background:#fff;'>
      @elseif (!empty($compositionFigure['error']))
        <p style='margin:0; color:#9b1c1c;'>圖檔產生失敗：{{ $compositionFigure['error'] }}</p>
      @endif
    </div>
  @endif
@elseif ($item === 'phenology')
  @if (($phenologySummary['survey_count'] ?? 0) === 0)
    <p style='margin:0;'>此資料範圍內尚無可彙整的種子雨調查資料。</p>
  @else
    <p style='margin:0 0 10px;'>
      調查期間共分別收到{{ $number($phenologySummary['flower_species_count']) }}種植物的花、{{ $number($phenologySummary['fruit_species_count']) }}種植物的種子、果實或果實碎片。檢視福山樣區從 {{ $phenologySummary['start_month_period_text'] }} 至 {{ $phenologySummary['end_month_period_text'] }} 調查期間各週開花、結果的物種數，{{ $phenologySummary['flower_peak_month_text'] }} 有較多種植物開花，{{ $phenologySummary['fruit_peak_month_text'] }} 收集到較多種植物的果實 (包括種子與碎片) (圖) 。後續我們將彙整長期的種子雨監測資料，比較研究期間開花、結果物種數與過去長期監測成果的異同。
    </p>

    <div style='margin-top:18px;'>
      <p style='margin:0 0 10px;'>
        圖. 福山森林動態樣區 {{ $phenologySummary['start_month_text'] }} 至 {{ $phenologySummary['end_month_text'] }} 各周開花與結果之物種數。圖中實線為開花物種數，虛線為結果物種數。
      </p>
      @if (!empty($phenologyFigure['png_url']))
        <div style='margin-bottom:10px;'>
          <a href='{{ $phenologyFigure['pdf_url'] }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 PDF</a>
        </div>
        <img src='{{ $phenologyFigure['png_url'] }}' alt='開花與結果物種數隨時間之變化圖' style='width:15cm; max-width:100%; height:auto; border:1px solid #ddd; background:#fff;'>
      @elseif (!empty($phenologyFigure['error']))
        <p style='margin:0; color:#9b1c1c;'>圖檔產生失敗：{{ $phenologyFigure['error'] }}</p>
      @endif
    </div>
  @endif
@elseif ($item === 'distribution')
  <p style='margin:0 0 10px;'>
    福山森林動態樣區內{{ $number($distributionSummary['trap_total']) }}個收集網，於 {{ $distributionSummary['start_month_period_text'] }} 至 {{ $distributionSummary['end_month_period_text'] }} 這段期間，{{ $distributionSummary['flower_trap_positive_count'] === $distributionSummary['trap_total'] ? '所有的網子都有' : '共有' . $number($distributionSummary['flower_trap_positive_count']) . '個網子' }}收集到花 (圖1a) ，其中{{ $number($distributionSummary['flower_trap_at_least_three_count']) }}個網子 ({{ $percent($distributionSummary['flower_trap_at_least_three_percent']) }}%) 收集到大於等於 3 種植物的花。出現在最多收集網的物種為{{ $distributionSummary['flower_top_species'] }}，共在{{ $number($distributionSummary['flower_top_species_trap_count']) }}個網子中發現該物種之花；包括{{ $distributionSummary['flower_top_species'] }}在內，共有{{ $number($distributionSummary['flower_species_at_least_ten_count']) }}種植物其落花分布在 10 個以上的收集網 (圖2) 。
  </p>

  <p style='margin:0 0 10px;'>
    在成熟果實及種子部分，研究期間有{{ $number($distributionSummary['fruit_trap_positive_count']) }}個網子 ({{ $percent($distributionSummary['fruit_trap_positive_percent']) }}%) 收集到成熟果實及種子 (圖1b) ，其中有{{ $number($distributionSummary['fruit_trap_at_least_three_count']) }}個網子收集到大於等於 3 種植物的種子或果實 ({{ $percent($distributionSummary['fruit_trap_at_least_three_percent']) }}%) 。比較不同植物其果實及種子能夠到達的收集網數，果實及種子分布最廣泛的植物是{{ $distributionSummary['fruit_top_species'] }}，其種子共在{{ $number($distributionSummary['fruit_top_species_trap_count']) }}個網子中發現。除了{{ $distributionSummary['fruit_top_species'] }}，共有{{ $number($distributionSummary['fruit_species_at_least_ten_count']) }}種植物其種子能散播到 10 個以上的收集網 (圖2) 。
  </p>

  <div style='margin-top:18px;'>
    <p style='margin:0 0 10px;'>
      圖 1. 福山森林動態樣區 {{ $distributionSummary['start_month_text'] }} 至 {{ $distributionSummary['end_month_text'] }} 各收集網所收集到的植物種數頻度分布圖。 (a) 為各收集網所收集到之花朵物種數、 (b) 則為成熟果實及種子之物種數。
    </p>
    @if (!empty($distributionFigure['png_url']))
      <div style='margin-bottom:10px;'>
        <a href='{{ $distributionFigure['pdf_url'] }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 PDF</a>
      </div>
      <img src='{{ $distributionFigure['png_url'] }}' alt='花、果實之空間分布圖' style='width:15cm; max-width:100%; height:auto; border:1px solid #ddd; background:#fff;'>
    @elseif (!empty($distributionFigure['error']))
      <p style='margin:0; color:#9b1c1c;'>圖檔產生失敗：{{ $distributionFigure['error'] }}</p>
    @endif
  </div>

  <div style='margin-top:18px;'>
    <p style='margin:0 0 10px;'>
      圖 2. 福山森林動態樣區 {{ $distributionSummary['start_month_text'] }} 至 {{ $distributionSummary['end_month_text'] }} 各植物之花朵和成熟果實及種子所分布之收集網數量。圖中黑色代表花朵、灰色代表成熟果實及種子。
    </p>
    @if (!empty($distributionSpeciesFigure['png_url']))
      <div style='margin-bottom:10px;'>
        <a href='{{ $distributionSpeciesFigure['pdf_url'] }}' target='_blank' class='datasavebutton' style='display:inline-block; width:auto; text-decoration:none;'>下載 PDF</a>
      </div>
      <img src='{{ $distributionSpeciesFigure['png_url'] }}' alt='各植物分布收集網數量圖' style='width:15cm; max-width:100%; height:auto; border:1px solid #ddd; background:#fff;'>
    @elseif (!empty($distributionSpeciesFigure['error']))
      <p style='margin:0; color:#9b1c1c;'>圖檔產生失敗：{{ $distributionSpeciesFigure['error'] }}</p>
    @endif
  </div>
@else
  <p style='margin:0;'>{{ $placeholder ?? '成果內容建置中。' }}</p>
@endif
