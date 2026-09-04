<div>
    <div class="text_box">
        <h2>GEO-TREES 資料比對</h2>
        <p style="margin: 10px 0">{{ $statusNote }}</p>
        <button type="button" wire:click="compare" wire:loading.attr="disabled">開始比對</button>
        <span wire:loading wire:target="compare" style="margin-left: 10px">比對中，請稍候……</span>
    </div>

    @if ($hasCompared)
        <div class="text_box" style="margin-top: 20px; overflow-x: auto">
            <h2>比對結果</h2>
            <p>可輸入資料共 {{ $eligible }} 筆；M 與 -- 共 {{ $locked }} 筆已排除。</p>
            @if ($differenceCount === 0)
                <p>兩次輸入資料皆相符。</p>
            @else
                <p>共發現 {{ $differenceCount }} 項缺漏或差異。</p>
                <table style="width: 100%; min-width: 850px; border-collapse: collapse">
                    <thead>
                        <tr>
                            <th>樣方</th><th>小樣區</th><th>stemid</th><th>項目</th>
                            <th>第一次輸入</th><th>第二次輸入</th><th>結果</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>({{ $row['qx'] }}, {{ $row['qy'] }})</td>
                                <td>({{ $row['sqx'] }}, {{ $row['sqy'] }})</td>
                                <td>{{ $row['stemid'] }}</td>
                                <td>{{ $row['field'] ?: '輸入狀態' }}</td>
                                <td>{{ $row['first'] }}</td><td>{{ $row['second'] }}</td>
                                <td>{{ $row['message'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($lastPage > 1)
                    <div style="margin-top: 14px">
                        <button type="button" wire:click="previousPage" @disabled($page <= 1)>上一頁</button>
                        <span style="margin: 0 10px">第 {{ $page }} / {{ $lastPage }} 頁</span>
                        <button type="button" wire:click="nextPage" @disabled($page >= $lastPage)>下一頁</button>
                    </div>
                @endif
            @endif
        </div>
    @endif
</div>
