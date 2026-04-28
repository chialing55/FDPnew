<div wire:init="loadUnknownData">
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>

    <h2>{{ $unk }} 資料檢視 / 更新 (限管理者)</h2>

    <div style='margin-top: 20px;'>
        <a class="unknown-card-button" href="{{ route('admin.fushan.seeds.unknown') }}">回 UNKNOWN</a>
    </div>

    <div class="text_box entrytableout" style="display: none;">


        <div id="simplenote" class="text_box2">
            <ul>
                <li><b>輸入資料後需按 <button class="datasavebutton" style="width: auto;">儲存</button> ，才能確實將資料儲存。</b></li>
                <li>這一頁只列出 {{ $unk }} 的全部資料。</li>
                <li>更新資料即為更新大表，請小心謹慎。</li>
            </ul>
        </div>

        <div class="entrytablediv">
            <div class="seedssavenote app-feedback-note"></div>
            <div id="seedstableout" class="seedstable fs100">
                <div class="pages">
                    <div class="totalnum"></div>
                    <div class="pagenote"></div>
                    <div class="prev">上一頁</div>
                    <div class="next">下一頁</div>
                </div>

                <div id="datatableunknown" class="fs100">
                    <div class="seedssavenote app-feedback-note"></div>
                    <p style="margin-top:5px; text-align: center"><button name="datasave2unknown" class="datasavebutton"
                            style="width:550px">儲存</button></p>
                </div>
            </div>
        </div>
    </div>
</div>
