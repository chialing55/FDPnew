<div>
    <h2>資料匯入 (限管理者)</h2>
    @if ($slmaxcensus != $nowcensus)
        <p style='margin: 10px 0; font-weight: 800'>seedling_records 大表中的最新資料為 第 {{ $slmaxcensus }} 次調查資料。<br>接下來要匯入 第
            {{ $nowcensus }} 次調查資料。</p>
    @else
        <p style='margin: 10px 0; font-weight: 800'>seedling_records 大表中的最新資料為 第 {{ $slmaxcensus }}
            次調查資料。<br>已將最新資料匯入，請至 <a href='{{ asset('/fushan/seedling/doc') }}'>相關文件</a> 產生新一次調查用之紀錄紙。</p>
    @endif
    <div class='text_box'>
        <h2>資料處理流程</h2>
        <hr>
        <ol>
            <li>完成<a href='{{ asset('/fushan/seedling/compare') }}'>資料比對</a></li>
            <li>進行特殊修改：修改 slrecord1。</li>

            <li>將小苗資料匯入大表 seedling_individuals, seedling_stems, seedling_records，將覆蓋度資料匯入 seedling_cov (slroll 沒有大表) 。
                @if (!empty($importCheckStatus))
                    <div style="margin: 10px 0 16px 0; padding: 10px; border: 1px solid #ddd; max-width: 520px;">
                        <div style="font-weight: 800; margin-bottom: 6px;">匯入前資料檢查</div>
                        <table style="border-collapse: collapse; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px;">seedling_records 最新 census
                                    </td>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px; text-align: right;">
                                        {{ $importCheckStatus['official_census'] ?? '無' }}</td>
                                </tr>
                                <tr>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px;">下一次應匯入 census</td>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px; text-align: right;">
                                        {{ $importCheckStatus['expected_census'] ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px;">slrecord1 census 範圍</td>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px; text-align: right;">
                                        {{ ($importCheckStatus['work_min_census'] ?? '-') . ' - ' . ($importCheckStatus['work_max_census'] ?? '-') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px;">slrecord1 筆數</td>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px; text-align: right;">
                                        {{ number_format($importCheckStatus['work_rows'] ?? 0) }}</td>
                                </tr>
                                <tr>
                                    <td style="border-top: 1px solid #eee; padding: 4px 6px;">slrecord1 未填日期筆數</td>
                                    <td
                                        style="border-top: 1px solid #eee; padding: 4px 6px; text-align: right; font-weight: 800; color: {{ ($importCheckStatus['missing_date_rows'] ?? 0) > 0 ? '#b00020' : '#167a30' }};">
                                        {{ number_format($importCheckStatus['missing_date_rows'] ?? 0) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
                @if ($importCensusWarning !== '')
                    <p style="margin: 10px 0 16px 0; font-weight: 800; color: #b00020;">{{ $importCensusWarning }}</p>
                @endif
                @if ($slmaxcensus != $nowcensus)
                    <p style='margin: 10px 0 30px 0'><button class="recruitbutton" wire:click="import"
                            wire:loading.attr="disabled" @if ($importCensusWarning !== '') disabled @endif>匯入大表</button>
                    </p>
                @else
                    <p style='margin: 10px 0 30px 0'><button class='recruitbutton' type="button"
                            disabled>已匯入大表</button></p>
                @endif
                <div class="loading-container" wire:loading.class="visible">
                    <div class="loading-spinner"></div>
                </div>
            </li>

            <li>後續資料表整理
                <ul style="list-style-type: disc; margin: 8px 0 12px 22px; padding-left: 18px;">
                    <li style='color: #b00020;'>尚未確認完整功能運作，202608資料要小心檢查。</li>
                    <li>備份 slrecord2(才有完整調查資料)、slcov1、slroll1 為 slrecord_yyyymm、slcov_yyyymm、slroll_yyyymm。</li>
                    <li>清空 slrecord、slrecord1、slrecord2、slcov1、slcov2、slroll1、slroll2 工作表，不刪除資料表。</li>
                    <li>清空並重建完整的 seedling 分析資料表。</li>
                    <li>將重建完成的 seedling 備份為 seedling_yyyymm。</li>
                </ul>
                @if ($cleanupBackupSuffix !== '')
                    <div style="margin: 10px 0 16px 0; padding: 10px; border: 1px solid #ddd; max-width: 520px;">
                        <div style="font-weight: 800; margin-bottom: 6px;">本次預計備份年月：{{ $cleanupBackupSuffix }}</div>
                        <table style="border-collapse: collapse; width: 100%;">
                            <tbody>
                                @foreach ($cleanupBackupStatus as $backup)
                                    <tr>
                                        <td style="border-top: 1px solid #eee; padding: 4px 6px;">
                                            {{ $backup['table'] }}</td>
                                        <td
                                            style="border-top: 1px solid #eee; padding: 4px 6px; font-weight: 800; color: {{ $backup['exists'] ? '#9a5b00' : '#167a30' }};">
                                            {{ $backup['exists'] ? '已存在' : '尚未產生' }}
                                        </td>
                                        <td style="border-top: 1px solid #eee; padding: 4px 6px; text-align: right;">
                                            {{ $backup['exists'] ? number_format($backup['count']) . ' 筆' : '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p style="margin: 10px 0 16px 0; font-weight: 800;">目前沒有可整理的工作表資料。</p>
                @endif
                @if ($cleanupCensusWarning !== '')
                    <p style="margin: 10px 0 16px 0; font-weight: 800; color: #b00020;">{{ $cleanupCensusWarning }}</p>
                @endif
                <p style='margin: 10px 0 30px 0'>
                    <button class="recruitbutton" wire:click="cleanupWorkTables" wire:loading.attr="disabled"
                        @if (!$canCleanupWorkTables) disabled @endif>自動整理資料表</button>
                </p>
            </li>
        </ol>
        <p style='margin: 10px 0; font-weight: 800'>以上完成後即可進 <a href='{{ asset('/fushan/seedling/doc') }}'>相關文件</a>
            產生新一期調查用的紀錄紙</p>


        @if (isset($importnote))
            <p>{{ $importnote }}</p>
        @endif
        @if (isset($cleanupnote))
            <p>{{ $cleanupnote }}</p>
        @endif
    </div>

</div>
