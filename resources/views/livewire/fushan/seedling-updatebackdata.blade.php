<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>

    @php
        $addclass1 = $type === '1' ? 'thistype' : '';
        $addclass2 = $type === '2' ? 'thistype' : '';
    @endphp

    <div class='seedling-update-mode-switch' style='display: flex; flex-wrap: wrap; justify-content: center;'>
        <div class='text_box seedling-update-mode-card {{ $addclass1 }}'>
            <h2>特殊修改</h2>
            <hr>
            <div style='display: inline-flex; align-items: center;'>
                <div>slrecord1 有特殊修改的資料：{{ $alternoteCount }} 筆</div>
                <div style='margin-left:20px'>
                    <form wire:submit.prevent='alternote'>
                        <button type="submit">進入特殊修改</button>
                    </form>
                </div>
            </div>
        </div>

        <div class='text_box seedling-update-mode-card {{ $addclass2 }}'>
            <h2>個別 tag 修改</h2>
            <hr>
            <div style='display: inline-flex; align-items: center;'>
                <div>輸入小苗 tag：</div>
                <div style="margin-left:20px;">
                    <form wire:submit.prevent="indTag">
                        <input name="tag" class="fs100" placeholder="tag" wire:model.defer="tag" style="width: 120px; text-transform: uppercase;">
                        <button type="submit" style="margin-left: 20px;">GO</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($go !== '')
        @if ($go === 'no')
            <div class='text_box'>
                <div class='tablenote'>
                    <span style='margin-right: 20px'>{{ $dataNote }}</span>
                </div>
            </div>
        @else
            <div id='simplenote' class='text_box' style='max-width: 900px;'>
                <ul>
                    <li>上方表格為 slrecord1 工作表資料；正式表資料拆成 seedling_individuals / seedling_stems 基本資料，以及 seedling_records 調查資料。</li>
                    <li>trap、plot、x、y、csp 會更新 seedling_individuals；mtag、tag、ind、sprout 會更新 seedling_stems；調查資料會更新 seedling_records。</li>
                    <li>若修改 tag 或 mtag，請先確認資料檢視中沒有重號。</li>
                    <li>刪除資料分為刪除全部資料與刪除單筆資料；若在特殊修改介面中要刪除單筆資料，請先刪除 seedling_records 的資料，再刪除 slrecord1 的資料。</li>
                </ul>
            </div>

            <div class='text_box' style='position: relative; min-width: 900px;'>
                @if ($taglist === [])
                    <div class='tablenote'>
                        <span style='margin-right: 20px'>沒有可修改資料</span>
                    </div>
                @else
                    @if ($type === '1')
                        @php($key = array_search($updateTag, $taglist, true))
                        <div style='display: flex; justify-content: space-between; align-items: center;'>
                            <div class='totalnum'>共有 {{ count($taglist) }} 筆資料需進行特殊修改</div>
                            <div>
                                <span style='margin-left:20px'>{{ ($key === false ? 0 : $key + 1) }} / {{ count($taglist) }}</span>
                                @if ($key !== false && $key > 0)
                                    <span style='margin-left:40px'><button type="button" class="a_" wire:click="searchTag({{ $key - 1 }})" wire:loading.attr="disabled" wire:target="searchTag" style="border:0; background:transparent; padding:0; cursor:pointer;">上一筆</button></span>
                                @endif
                                @if ($key !== false && $key < count($taglist) - 1)
                                    <span style='margin-left:30px'><button type="button" class="a_" wire:click="searchTag({{ $key + 1 }})" wire:loading.attr="disabled" wire:target="searchTag" style="border:0; background:transparent; padding:0; cursor:pointer;">下一筆</button></span>
                                @endif
                                <span style='margin-left:30px;'>直接前往：
                                    <input name="goto" class="fs100" wire:model.defer="goto" wire:change="searchTag($event.target.value - 1)" style="width: 40px;">
                                </span>
                            </div>
                        </div>
                    @endif

                    <div style='display: flex; align-items: center; gap: 32px; margin-top: 12px;'>
                        <h2 style='margin: 0;'>{{ $type === '2' ? 'mtag: ' . $updateMtag : $updateTag }}</h2>
                        <button type='button' name='seedlingUpdateDeleteAll' class='datasavebutton' style='width:auto;'>刪除全部資料</button>
                        <span class='seedlingupdatesavenote savenote app-feedback-note {{ $dataNoteType === "success" ? "app-feedback-note--success" : ($dataNoteType === "error" ? "app-feedback-note--error" : "") }}'>{{ $dataNote }}</span>
                    </div>

                    <script type="application/json" id="seedlingUpdatePayload">{!! json_encode([
                        'tag' => $updateTag,
                        'from' => $from,
                        'workRows' => $workRows,
                        'identityRows' => $identityRows,
                        'masterRows' => $masterRows,
                        'csplist' => $csplist,
                    ], JSON_UNESCAPED_UNICODE) !!}</script>

                    <div style='margin-top: 15px;'>
                        <h3 style='margin:0;'>slrecord1</h3>
                    </div>
                    <div id='seedlingUpdateWorkTable' wire:ignore style='margin-top: 10px; min-height: 70px;' class='fs100'></div>

                    <div style='margin-top: 25px;'>
                        <h3 style='margin:0;'>seedling_individuals / seedling_stems</h3>
                    </div>
                    <div id='seedlingUpdateIdentityTable' wire:ignore style='margin-top: 10px; min-height: 70px;' class='fs100'></div>

                    <div style='margin-top: 25px; display:flex; align-items:center; gap:14px;'>
                        <h3 style='margin:0;'>seedling_records</h3>
                        @if ($type === '2')
                            <button type='button' name='seedlingUpdateSortTag' class='datasavebutton' style='width:auto;'>依 tag 排序</button>
                            <button type='button' name='seedlingUpdateSortCensus' class='datasavebutton' style='width:auto;'>依 census 排序</button>
                        @endif
                    </div>
                    <div id='seedlingUpdateMasterTable' wire:ignore style='margin-top: 10px; min-height: 70px;' class='fs100'></div>

                    <p style='margin-top:12px; text-align: center;'>
                        <button name='seedlingUpdateSave' class='datasavebutton'>儲存</button>
                    </p>
                @endif
            </div>
        @endif
    @endif
</div>
