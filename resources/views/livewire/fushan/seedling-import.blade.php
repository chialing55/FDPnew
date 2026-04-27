<div>
    <h2>資料匯入 (限管理者)</h2>
    @if($slmaxcensus!= $nowcensus)
    <p style='margin: 10px 0; font-weight: 800'>seedling 資料表中的最新資料為 第 {{$slmaxcensus}} 次調查資料。<br>接下來要匯入 第 {{$nowcensus}} 次調查資料。</p>
    @else
    <p style='margin: 10px 0; font-weight: 800'>seedling 資料表中的最新資料為 第 {{$slmaxcensus}} 次調查資料。<br>已將最新資料匯入，請至 <a href='{{asset('/fushan/seedling/doc')}}'>相關文件</a> 產生新一次調查用之紀錄紙。</p>
    @endif
    <div class='text_box'>
        <h2>資料處理流程</h2>
        <hr>
        <p>
            <ol>
                <li>完成<a href='{{asset('/fushan/seedling/compare')}}'>資料比對</a></li>
                <li>(後端) 進行特殊修改：修改 slrecord1，並刪除 slrecord1 的 alternote 欄位資料，以示以完成特殊修改。</li>
            
                <li>將小苗資料匯入大表 seedling，將覆蓋度資料匯入 seedling_cov，將小苗位置資料匯入/更新至 base (slroll 沒有大表) 。</li>
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
                <li>備份 slrecord2(才有特殊修改資訊)、slcov1、slroll1 為 slrecord_yyyymm、slcov_yyyymm、slroll_yyyymm。</li>
                <li>清空 slrecord、slrecord1、slrecord2、slcov1、slcov2、slroll1、slroll2 工作表，不刪除資料表。</li>
                <li>複製 seedling 資料表，名為 seedling_yyyymm(最新調查年月)以做備份。</li>
                <li>複製 base 資料表，名為 base_yyyymm(最新調查年月)以做備份。</li>
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
