<div>
    <h2>資料匯入 (限管理者)</h2>
    @if($slmaxcensus!= $nowcensus)
    <p style='margin: 10px 0; font-weight: 800'>seedling_records 大表中的最新資料為 第 {{$slmaxcensus}} 次調查資料。<br>接下來要匯入 第 {{$nowcensus}} 次調查資料。</p>
    @else
    <p style='margin: 10px 0; font-weight: 800'>seedling_records 大表中的最新資料為 第 {{$slmaxcensus}} 次調查資料。<br>已將最新資料匯入，請至 <a href='{{asset('/fushan/seedling/doc')}}'>相關文件</a> 產生新一次調查用之紀錄紙。</p>
    @endif
    <div class='text_box'>
        <h2>資料處理流程</h2>
        <hr>
        <p>
            <ol>
                <li>完成<a href='{{asset('/fushan/seedling/compare')}}'>資料比對</a></li>
                <li>進行特殊修改：修改 slrecord1。</li>
            
                <li>將小苗資料匯入大表 seedling_individuals, seedling_stems, seedling_records，將覆蓋度資料匯入 seedling_cov (slroll 沒有大表) 。</li>
            </ol>
            @if($slmaxcensus != $nowcensus)
                <p style='margin: 10px 0 30px 0'><button class='recruitbutton' wire:click="import" wire:loading.attr="disabled">匯入大表</button></p>
            @else
                <p style='margin: 10px 0 30px 0'><button class='recruitbutton' type="button" disabled>已匯入大表</button></p>
            @endif
        </p>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
        <p>

        <h6>後續資料表整理 (自動)</h6>
            <ol>
                <li>備份 slrecord2(才有完整調查資料)、slcov1、slroll1 為 slrecord_yyyymm、slcov_yyyymm、slroll_yyyymm。</li>
                <li>清空 slrecord、slrecord1、slrecord2、slcov1、slcov2、slroll1、slroll2 工作表，不刪除資料表。</li>
                <li>清空並重建完整的 seedling 分析資料表。</li>
                <li>將重建完成的 seedling 備份為 seedling_yyyymm。</li>
            </ol>
            <p style='margin: 10px 0 30px 0'>
                <button class='recruitbutton' wire:click="cleanupWorkTables" wire:loading.attr="disabled">自動整理資料表</button>
            </p>

        </p>
        <p style='margin: 10px 0; font-weight: 800'>以上完成後即可進 <a href='{{asset('/fushan/seedling/doc')}}'>相關文件</a> 產生新一期調查用的紀錄紙</p>

    
    @if(isset($importnote))
        <p >{{$importnote}}</p>
    @endif
    @if(isset($cleanupnote))
        <p >{{$cleanupnote}}</p>
    @endif
    </div>

</div>
