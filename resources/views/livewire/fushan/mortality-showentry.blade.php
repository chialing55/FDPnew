<div id="mortality-entry-root">
    <div>
        <h2>{{ $surveyYear ?? '—' }} 年 第 {{ $currentCensus ?? '—' }} 次調查 - 第 {{ $entry }} 次資料輸入</h2>

        <div style="margin-top:10px;">

            <p style="margin:0;">
                <a href="{{ route('admin.fushan.mortality.note') }}"
                    style="color:black; font-weight:700; text-decoration:none;">請先詳閱死亡率調查輸入注意事項</a>
            </p>

            <p style="margin-top:10px;">輸入者 {{ $user }}，輸入日期 {{ $inputDate }}</p>
        </div>

        @if (!empty($mapOptions))
            <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:18px;">
                <span style="font-weight:700;">選擇要輸入的 map</span>
                <span style="font-weight:700;">
                    <select id="mortality-entry-page" wire:model.live="selectedMapKey"
                        onchange="if (window.mortalitySelectFallback) { window.mortalitySelectFallback(this); }"
                        style="min-width:72px; height:32px; padding:4px 8px;">
                        <option value=""> </option>
                        @foreach ($mapOptions as $option)
                            <option value="{{ $option['key'] }}" @selected((string) $selectedMapKey === (string) $option['key'])>
                                {{ $option['map'] }}
                            </option>
                        @endforeach
                    </select>
                </span>



                @if ($selectedMapSort !== null && $selectedMap !== null)
                    <span style="display:inline-flex; gap:20px; min-width:140px; margin-left:12px; font-weight:700;">
                        <span style="display:inline-block; min-width:52px;">
                            @if ($previousMapKey !== null)
                                <a href="#" wire:click.prevent="loadMapSort('{{ $previousMapKey }}')"
                                    style="color:#374151; text-decoration:none;">上一個</a>
                            @endif
                        </span>
                        <span style="display:inline-block; min-width:52px;">
                            @if ($nextMapKey !== null)
                                <a href="#" wire:click.prevent="loadMapSort('{{ $nextMapKey }}')"
                                    style="color:#374151; text-decoration:none;">下一個</a>
                            @endif
                        </span>
                    </span>
                @endif

            </div>
            <div style='margin:10px 0'>
                @if (!empty($completionHint))
                    <span style="font-weight:700; color:#374151;">{{ $completionHint }}</span>
                @elseif ($selectedMapKey === null && $firstPendingMapKey !== null)
                    <span style="color:#475569;">建議從 map {{ $firstPendingMap }} 開始。</span>
                @endif
            </div>
        @else
            <div style="margin-top:14px; color:#475569;">
                目前尚無可輸入資料，請先建立 `record1` / `record2`。
            </div>
        @endif

    </div>

    @if ($selectedMapSort !== null && $selectedMap !== null)
        <div id="simplenote" class="text_box" style="margin-bottom:16px;">
            <div style="font-weight:700; margin-bottom:8px;">輸入提醒</div>
            <ul style="margin:0; padding-left:22px; line-height:1.8;">
                <li>輸入前請先閱讀<a href="{{ route('admin.fushan.mortality.note') }}"><b>死亡率調查輸入注意事項</b></a>。</li>
                <li>輸入資料後請按「儲存」，確認資料已確實保存。</li>
                <li>若現場特殊狀況無法符合一般檢查規則，但確認需要照實記錄，請在備註中選擇 <b>特例 / exception</b>，並簡要說明原因。</li>
            </ul>
        </div>

        <div class='text_box'>
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div style="font-weight:700;">調查團隊</div>
                <button type="button" wire:click="toggleTeamBuilder"
                    style="height:32px; padding:0 12px; border:1px solid #94a3b8; border-radius:6px; background:#fff; color:#334155;">
                    {{ $showTeamBuilder ? '收合團隊表' : '建立 / 查詢團隊' }}
                </button>
            </div>

            @if ($showTeamBuilder)
                <form wire:submit.prevent="createTeamFromBuilder"
                    style="display:flex; flex-direction:column; gap:12px; margin-top:12px; margin-bottom:14px; color:#334155; line-height:1.8;">
                    <div style="display:flex; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                        <span style="font-weight:700; min-width:72px; padding-top:4px;">
                            {{ $editingTeamId !== '' ? '修改團隊' : '建立團隊' }}
                        </span>

                        <div style="display:flex; align-items:flex-start; gap:8px; flex-wrap:wrap; flex:1 1 700px;">
                            @if ($editingTeamId !== '')
                                <span style="height:34px; display:inline-flex; align-items:center; font-weight:700; color:#334155;">
                                    team_id {{ $editingTeamId }}
                                </span>
                            @endif
                            @foreach ($surveyPersonnel as $index => $personName)
                                <input type="text" list="mortality-person-options"
                                    wire:model.defer="surveyPersonnel.{{ $index }}"
                                    wire:key="mortality-person-{{ $index }}" placeholder="調查人員 {{ $index + 1 }}"
                                    style="width:110px; height:34px; padding:4px 8px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                            @endforeach

                            <button type="button" wire:click="addPersonnelField"
                                style="height:34px; padding:0 12px; border:1px solid #94a3b8; border-radius:6px; background:#fff; color:#334155; white-space:nowrap;">
                                增加一位
                            </button>
                            <span style="margin-left:40px; display:inline-flex; align-items:center;">
                                <button type="submit" class="datasavebutton"
                                    style="height:34px; padding:0 12px; width:auto; display:inline-flex; align-items:center; border-radius:6px; box-sizing:border-box;">
                                    新增團隊
                                </button>
                            </span>
                            @if ($editingTeamId !== '')
                                <button type="button" wire:click="updateEditingTeam"
                                    style="height:34px; padding:0 12px; border:1px solid #94a3b8; border-radius:6px; background:#fff; color:#334155; white-space:nowrap;">
                                    儲存修改
                                </button>
                            @endif
                            <button type="button" wire:click="startNewTeam"
                                style="height:34px; padding:0 12px; border:1px solid #94a3b8; border-radius:6px; background:#fff; color:#334155; white-space:nowrap;">
                                清空欄位
                            </button>
                            @if ($editingTeamId !== '')
                                <button type="button" wire:click="deleteEditingTeam"
                                    onclick="return confirm('確定要刪除 team_id {{ $editingTeamId }}？')"
                                    style="height:34px; padding:0 12px; border:1px solid #dc2626; border-radius:6px; background:#fff; color:#b91c1c; white-space:nowrap;">
                                    刪除團隊
                                </button>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p style='font-size:14px; color:#64748b; margin:0;'>* 可先搜尋下方 team_id 或人名，點選團隊後會帶入人員清單。建立或修改團隊後，請在下方資料表的 Team 欄填寫 team_id。</p>
                    </div>
                    @error('surveyPersonnel')
                        <div style="color:#b91c1c;">{{ $message }}</div>
                    @enderror
                    @if (!empty($surveyMetaMessage))
                        <div style="color:#166534;">{{ $surveyMetaMessage }}</div>
                    @endif
                </form>

                <div style="display:flex; align-items:center; gap:10px; margin:8px 0;">
                    <span style="font-weight:700;">查詢調查人員</span>
                    <input type="text" wire:model.live="teamSearch" placeholder="team_id 或人名"
                        style="width:220px; height:32px; padding:4px 8px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                </div>

                <div style="max-height:180px; overflow:auto; border:1px solid #e2e8f0; border-radius:6px;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="text-align:left; padding:0; border-bottom:1px solid #e2e8f0;">
                                    <button type="button" wire:click="sortTeamOptions('id')"
                                        style="width:100%; padding:6px 8px; border:0; background:transparent; text-align:left; font-weight:700; cursor:pointer;">
                                        team_id{{ $this->teamSortMarkers['id'] }}
                                    </button>
                                </th>
                                <th style="text-align:left; padding:0; border-bottom:1px solid #e2e8f0;">
                                    <button type="button" wire:click="sortTeamOptions('label')"
                                        style="width:100%; padding:6px 8px; border:0; background:transparent; text-align:left; font-weight:700; cursor:pointer;">
                                        團隊人員{{ $this->teamSortMarkers['label'] }}
                                    </button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->filteredTeamOptions as $teamOption)
                                <tr wire:key="mortality-team-option-{{ $teamOption['id'] }}"
                                    wire:click="loadTeamForEditing({{ $teamOption['id'] }})"
                                    style="cursor:pointer; @if((string) $editingTeamId === (string) $teamOption['id']) background:#ecfdf5; @endif">
                                    <td style="padding:5px 8px; border-bottom:1px solid #f1f5f9; width:80px; font-weight:700;">{{ $teamOption['id'] }}</td>
                                    <td style="padding:5px 8px; border-bottom:1px solid #f1f5f9;">{{ $teamOption['label'] }}</td>
                                </tr>
                            @endforeach
                            @if (empty($this->filteredTeamOptions))
                                <tr>
                                    <td colspan="2" style="padding:8px; color:#64748b;">沒有符合的團隊。</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <div class='text_box '>

            <div id="mortality-entry-hot-shell" style="display:inline-flex; flex-direction:column; margin-top:12px;">
                <div style="color:#475569; line-height:1.8; margin-bottom:12px;">
                    {{-- <div>目前排序序號：{{ $selectedMapSort }}</div> --}}
                    <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
                        <span>共有 {{ $recordCount }} 筆資料。</span>
                        <span id="mortality-page-note"></span>
                        <span style="display:inline-block; min-width:52px;">
                            <a href="#" id="mortality-page-prev"
                                onclick="window.mortalityPrevPage && window.mortalityPrevPage(); return false;"
                                style="visibility:hidden; color:#374151; text-decoration:none;">上一頁</a>
                        </span>
                        <span style="display:inline-block; min-width:52px;">
                            <a href="#" id="mortality-page-next"
                                onclick="window.mortalityNextPage && window.mortalityNextPage(); return false;"
                                style="visibility:hidden; color:#374151; text-decoration:none;">下一頁</a>
                        </span>
                    </div>
                    @if (!empty($entrySaveMessage))
                        <div style="margin-top:8px; color:#166534;">{{ $entrySaveMessage }}</div>
                    @endif
                    @if (!empty($mainStatusMessage))
                        <div style="margin-top:8px; color:#166534;">{{ $mainStatusMessage }}</div>
                    @endif
                    @if (!empty($entrySaveErrors))
                        <div style="margin-top:8px; color:#b91c1c;">
                            @foreach ($entrySaveErrors as $saveError)
                                <div>{{ $saveError }}</div>
                            @endforeach
                        </div>
                    @endif
                    {{-- <div>本頁尚未填寫 status 筆數：{{ $pendingCount }}</div>
                    <div>本頁輸入狀態：{{ $currentPageCompleted ? '已完成' : '尚未完成' }}</div> --}}
                </div>
                @if (empty($records))
                    <div style="color:#475569;">這個 map 目前沒有資料。</div>
                @else
                    <div id="mortality-entry-hot" wire:ignore></div>
                    <p style="margin-top:5px; text-align:center;">
                        <button type="button" class="datasavebutton"
                            onclick="window.mortalitySavePage && window.mortalitySavePage(); return false;">
                            儲存
                        </button>
                    </p>
                @endif
            </div>
        </div>

        <datalist id="mortality-person-options">
            @foreach ($personOptions as $personOption)
                <option value="{{ $personOption }}"></option>
            @endforeach
        </datalist>
    @endif

    @if ($commentsModalOpen)
        <div style="position:fixed; inset:0; background:rgba(15,23,42,.42); z-index:1200; padding:24px; overflow:auto;">
            <div
                style="width:min(980px, 96vw); margin:0 auto; background:#fff; border-radius:10px; box-shadow:0 20px 60px rgba(15,23,42,.22); overflow:hidden;">
                <div
                    style="padding:16px 18px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; gap:16px; align-items:center;">
                    <div>
                        <div style="font-size:20px; font-weight:700;">新增備註</div>
                        <div style="margin-top:4px; color:#64748b;">
                            {{ $editingCommentMeta['stemid'] ?? '—' }} / {{ $editingCommentMeta['csp'] ?? '—' }}
                        </div>
                    </div>
                    <button type="button" wire:click="closeCommentsEditor"
                        style="border:0; background:transparent; font-size:20px; cursor:pointer;">X</button>
                </div>

                <form wire:submit.prevent="saveCommentsEditor" style="padding:18px;">
                    <div
                        style="display:grid; grid-template-columns:minmax(320px, 1fr) minmax(260px, 360px); gap:18px; align-items:start;">
                        <div>
                            <div style="font-size:12px; color:#64748b; margin-bottom:8px;">Comments</div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                @foreach ($commentItems as $itemIndex => $item)
                                    <div wire:key="comment-item-{{ $itemIndex }}"
                                        style="display:grid; grid-template-columns:220px minmax(180px, 1fr); gap:8px; align-items:center;">
                                        <select wire:model.defer="commentItems.{{ $itemIndex }}.comment_id"
                                            class="js-mortality-comment-option-select"
                                            style="width:220px; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box; background:#fff;">
                                            <option value="">選擇 option</option>
                                            @foreach ($commentOptions as $optionIndex => $option)
                                                @if (!empty($option['is_divider']))
                                                    <option value="__divider_{{ $optionIndex }}" disabled>──────────
                                                    </option>
                                                @else
                                                    <option value="{{ $option['id'] }}"
                                                        data-full-label="{{ $option['label'] }}"
                                                        data-short-label="{{ $option['short_label'] }}">
                                                        {{ (string) ($item['comment_id'] ?? '') === (string) $option['id'] ? $option['short_label'] : $option['label'] }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <input type="text"
                                            wire:model.defer="commentItems.{{ $itemIndex }}.text"
                                            placeholder="note"
                                            style="width:100%; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;">
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" wire:click="addCommentItemRow"
                                style="margin-top:10px; padding:8px 14px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:#fff; cursor:pointer;">
                                新增一列
                            </button>
                            <button type="button" wire:click="toggleCommentOptionForm"
                                style="margin-top:10px; margin-left:8px; padding:8px 14px; border:1px solid rgba(180,83,9,.35); border-radius:6px; background:#f59e0b; color:#fff; cursor:pointer;">
                                {{ $showCommentOptionForm ? '收合新增 option' : '新增 option' }}
                            </button>

                            @if ($showCommentOptionForm)
                                <div
                                    style="margin-top:12px; padding:12px; border:1px solid rgba(0,0,0,.08); border-radius:8px; background:#f8fafc;">
                                    <div
                                        style="display:grid; grid-template-columns:120px 1fr; gap:10px; align-items:center;">
                                        <div>中文</div>
                                        <input type="text" wire:model.defer="newCommentOption.comment_zh"
                                            style="width:100%; height:36px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                                        <div>英文</div>
                                        <input type="text" wire:model.defer="newCommentOption.comment_en"
                                            style="width:100%; height:36px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                                        <div>Category</div>
                                        <select wire:model.defer="newCommentOption.category"
                                            style="width:100%; height:36px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box; background:#fff;">
                                            <option value="">選擇 category</option>
                                            <option value="stem_condition">stem_condition</option>
                                            <option value="POM_issue">POM_issue</option>
                                            <option value="structural_change">structural_change</option>
                                            <option value="biotic_damage">biotic_damage</option>
                                            <option value="disease">disease</option>
                                            <option value="other">other</option>
                                        </select>
                                        <div>Code</div>
                                        <input type="text" wire:model.defer="newCommentOption.code"
                                            style="width:100%; height:36px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                                    </div>

                                    @error('newCommentOption.comment_zh')
                                        <div style="margin-top:8px; color:#b91c1c;">{{ $message }}</div>
                                    @enderror
                                    @error('newCommentOption.comment_en')
                                        <div style="margin-top:8px; color:#b91c1c;">{{ $message }}</div>
                                    @enderror
                                    @error('newCommentOption.code')
                                        <div style="margin-top:8px; color:#b91c1c;">{{ $message }}</div>
                                    @enderror

                                    @if (!empty($commentOptionMessage))
                                        <div style="margin-top:8px; color:#166534;">{{ $commentOptionMessage }}</div>
                                    @endif

                                    <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                                        <button type="button" wire:click="createCommentOption"
                                            style="padding:8px 14px; border:0; border-radius:6px; background:#3f5f5b; color:#fff; cursor:pointer;">
                                            儲存 option
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div>
                            <div style="font-size:12px; color:#64748b; margin-bottom:8px;">特殊修改</div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                @foreach ($stemCorrectionItems as $itemIndex => $item)
                                    <div wire:key="stem-correction-item-{{ $itemIndex }}"
                                        style="display:grid; grid-template-columns:180px 112px; gap:8px; align-items:center; justify-content:start;">
                                        <select wire:model.defer="stemCorrectionItems.{{ $itemIndex }}.field"
                                            style="width:180px; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box; background:#fff;">
                                            <option value="">選擇欄位</option>
                                            @foreach ($stemCorrectionOptions as $fieldValue => $fieldLabel)
                                                <option value="{{ $fieldValue }}">{{ $fieldLabel }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text"
                                            wire:model.defer="stemCorrectionItems.{{ $itemIndex }}.text"
                                            placeholder="text"
                                            style="width:112px; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;">
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" wire:click="addStemCorrectionItemRow"
                                style="margin-top:10px; padding:8px 14px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:#fff; cursor:pointer;">
                                新增一列
                            </button>
                        </div>
                    </div>
                    <div style="margin-top:18px; display:flex; justify-content:flex-end; gap:10px;">
                        <button type="submit"
                            style="padding:10px 18px; border:0; border-radius:6px; background:#3f5f5b; color:#fff; cursor:pointer;">
                            儲存備註
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @once
        <style>
            #mortality-entry-hot .mortality-hot-header {
                height: 128px;
                display: flex;
                align-items: flex-end;
                justify-content: center;
                overflow: hidden;
                padding: 0;
                box-sizing: border-box;
            }

            #mortality-entry-hot .mortality-hot-header-text {
                writing-mode: vertical-rl;
                transform: rotate(180deg);
                white-space: nowrap;
                line-height: 1.05;
                display: inline-block;
                font-weight: 500;
                font-size: 11px;
            }

            #mortality-entry-hot .handsontable td {
                white-space: nowrap;
            }

            #mortality-entry-hot .mortality-divider-right {
                border-right: 2px solid #94a3b8 !important;
            }

            #mortality-entry-hot .mortality-divider-left {
                border-left: 2px solid #94a3b8 !important;
            }

            #mortality-entry-hot .mortality-error-cell {
                background: #fee2e2 !important;
            }

            #mortality-entry-hot .mortality-error-row-header {
                background: #fecaca !important;
                color: #991b1b !important;
            }

            .handsontableEditor.listbox .wtHolder {
                max-height: 180px !important;
            }

            #mortality-entry-hot .mortality-note-button {
                border: 1px solid #cbd5e1;
                border-radius: 6px;
                background: #f8fafc;
                color: #334155;
                cursor: pointer;
            }

            #mortality-entry-hot .mortality-note-cell {
                display: flex;
                align-items: center;
                gap: 6px;
                min-width: 0;
            }

            #mortality-entry-hot .mortality-note-summary {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                color: #334155;
                font-size: 12px;
            }
        </style>

        <script>
            (() => {
                if (window.__mortalityEntryHotBound) {
                    return;
                }
                window.__mortalityEntryHotBound = true;

                let mortalityHot = null;
                let currentRecords = [];
                let currentEnabled = false;
                let currentTeamOptions = [];
                let livewireListenerBound = false;
                let currentHotPage = 1;
                let errorRecordIds = [];
                const pageSize = 20;
                const paginationThreshold = 25;
                const findMortalityComponentId = (startElement = null) => {
                    let node = startElement instanceof Element ? startElement : document.getElementById(
                        'mortality-entry-root');

                    while (node) {
                        if (typeof node.getAttribute === 'function') {
                            const wireId = node.getAttribute('wire:id');
                            if (wireId) {
                                return wireId;
                            }
                        }
                        node = node.parentElement;
                    }

                    const root = document.getElementById('mortality-entry-root');
                    if (root) {
                        const nestedComponent = root.querySelector('[wire\\:id]');
                        if (nestedComponent) {
                            return nestedComponent.getAttribute('wire:id');
                        }
                    }

                    const anyComponent = document.querySelector('[wire\\:id]');
                    return anyComponent ? anyComponent.getAttribute('wire:id') : null;
                };
                const integerRangeValidator = (min, max) => function(value, callback) {
                    if (value === null || value === undefined || value === '') {
                        callback(true);
                        return;
                    }

                    const stringValue = String(value).trim();
                    if (!/^-?\d+$/.test(stringValue)) {
                        callback(false);
                        return;
                    }

                    const numberValue = Number(stringValue);
                    callback(numberValue >= min && numberValue <= max);
                };

                const decimalValidator = function(value, callback) {
                    if (value === null || value === undefined || value === '') {
                        callback(true);
                        return;
                    }

                    const stringValue = String(value).trim();
                    callback(/^-?\d+(\.\d{1,2})?$/.test(stringValue));
                };

                function normalizeTeamOptions(teamOptions) {
                    return (teamOptions || [])
                        .map((team) => ({
                            id: String(team.id ?? ''),
                            label: String(team.label ?? '').trim(),
                            display: String(team.display ?? `${team.id ?? ''}：${team.label ?? ''}`).trim(),
                        }))
                        .filter((team) => team.id !== '' && team.label !== '');
                }

                function teamLabelFromId(teamId) {
                    const id = String(teamId ?? '');
                    const team = currentTeamOptions.find((option) => option.id === id);
                    return team ? team.id : id;
                }

                function teamIdFromLabel(value) {
                    const text = String(value ?? '').trim();
                    if (text === '') {
                        return '';
                    }

                    const exact = currentTeamOptions.find((option) => option.label === text || option.id === text || option.display === text);
                    return exact ? exact.id : text;
                }

                function teamIdExists(teamId) {
                    const id = String(teamId ?? '').trim();
                    return id !== '' && currentTeamOptions.some((option) => option.id === id);
                }

                const teamIdValidator = function(value, callback) {
                    if (value === null || value === undefined || String(value).trim() === '') {
                        callback(false);
                        return;
                    }

                    callback(teamIdExists(value));
                };

                function teamRenderer(instance, td, row, col, prop, value) {
                    Handsontable.renderers.TextRenderer.apply(this, arguments);
                    const label = teamLabelFromId(value);
                    td.textContent = label;
                    const team = currentTeamOptions.find((option) => option.id === String(value ?? ''));
                    td.title = team ? `team_id: ${team.id}\n${team.label}` : label;
                    td.style.fontSize = '11px';
                    td.style.overflow = 'hidden';
                    td.style.textOverflow = 'ellipsis';
                    td.style.whiteSpace = 'nowrap';
                    return td;
                }

                function normalizeMortalityDate(value) {
                    const text = String(value ?? '').trim();
                    const match = text.match(/^\d{4}-\d{2}-\d{2}/);
                    return match ? match[0] : text;
                }

                function dateRenderer(instance, td, row, col, prop, value) {
                    Handsontable.renderers.TextRenderer.apply(this, arguments);
                    td.textContent = normalizeMortalityDate(value);
                    return td;
                }

                const columns = [{
                        data: 'map',
                        readOnly: true
                    },
                    {
                        data: 'date',
                        type: 'date',
                        dateFormat: 'YYYY-MM-DD',
                        correctFormat: true,
                        allowInvalid: false,
                        renderer: dateRenderer
                    },
                    {
                        data: 'team_id',
                        allowInvalid: false,
                        validator: teamIdValidator,
                        renderer: teamRenderer
                    },
                    {
                        data: 'q20',
                        readOnly: true
                    },
                    {
                        data: 'q10',
                        readOnly: true
                    },
                    {
                        data: 'stemid',
                        readOnly: true
                    },
                    {
                        data: 'csp',
                        readOnly: true
                    },
                    {
                        data: 'dbh1',
                        readOnly: true,
                        type: 'numeric'
                    },
                    {
                        data: 'dbh2',
                        type: 'numeric',
                        allowInvalid: false,
                        validator: decimalValidator
                    },
                    {
                        data: 'status',
                        type: 'dropdown',
                        source: ['', 'OK', 'A', 'D', 'X', 'NF'],
                        allowInvalid: false
                    },
                    {
                        data: 'mode',
                        type: 'autocomplete',
                        source: ['', 'S', 'B', 'U', 'SU', 'BU', '?'],
                        strict: false,
                        filter: false
                    },
                    {
                        data: 'living_length',
                        type: 'numeric',
                        numericFormat: {
                            pattern: '0.00'
                        },
                        allowInvalid: false,
                        validator: decimalValidator
                    },
                    {
                        data: 'branches',
                        type: 'numeric',
                        allowInvalid: false,
                        validator: integerRangeValidator(0, 100)
                    },
                    {
                        data: 'illumination',
                        type: 'dropdown',
                        source: ['', '0', '1', '2', '3', '4', '5'],
                        allowInvalid: false
                    },
                    {
                        data: 'leaning',
                        type: 'numeric',
                        allowInvalid: false,
                        validator: integerRangeValidator(10, 150)
                    },
                    {
                        data: 'liana',
                        type: 'dropdown',
                        source: ['', 'L', 'S', 'LS'],
                        allowInvalid: false
                    },
                    {
                        data: 'fungi',
                        type: 'dropdown',
                        source: ['', '1'],
                        allowInvalid: false
                    },
                    {
                        data: 'wounded_stem',
                        type: 'dropdown',
                        source: ['', '1', '2', '3'],
                        allowInvalid: false
                    },
                    {
                        data: 'deformity',
                        type: 'dropdown',
                        source: ['', '1', '2', '3'],
                        allowInvalid: false
                    },
                    {
                        data: 'rotten',
                        type: 'dropdown',
                        source: ['', '1', '2', '3'],
                        allowInvalid: false
                    },
                    {
                        data: 'leaves',
                        type: 'numeric',
                        allowInvalid: false,
                        validator: integerRangeValidator(0, 100)
                    },
                    {
                        data: 'leaf_damage',
                        type: 'dropdown',
                        source: ['', '1'],
                        allowInvalid: false
                    },
                    {
                        data: 'comments_button',
                        readOnly: true,
                        renderer: commentsButtonRenderer
                    },
                ];

                const colHeaders = [
                    'map',
                    'Date',
                    'Team',
                    'Q20',
                    'Q10',
                    'Tag',
                    'Sp',
                    'DBH (old)',
                    'DBH (new)',
                    'OK / A / D / X / NF',
                    'S / B / U',
                    'Living length (m)',
                    '% Branches',
                    'Illumination',
                    'Leaning',
                    'Lianas, strang.',
                    'Fungi',
                    'Wounded stem',
                    'Canker, deformity',
                    'Rotten trunk',
                    '% Leaves',
                    'Leave damage',
                    'Animal, other factors,<br> comments',
                ];

                const colWidths = [44, 116, 46, 54, 54, 86, 120, 60, 60, 58, 48, 58, 52, 52, 48, 54, 42, 54, 58, 52, 48, 54, 200];
                const hiddenColumnIndexes = [0];
                const visibleTableWidth = colWidths.reduce((total, width, index) => {
                    return hiddenColumnIndexes.includes(index) ? total : total + width;
                }, 38);

                function expandMortalityCommentOptionSelect(select) {
                    Array.from(select.options).forEach((option) => {
                        if (option.dataset.fullLabel) {
                            option.textContent = option.dataset.fullLabel;
                        }
                    });
                }

                function collapseMortalityCommentOptionSelect(select) {
                    Array.from(select.options).forEach((option) => {
                        if (!option.dataset.fullLabel) {
                            return;
                        }

                        option.textContent = option.selected ?
                            (option.dataset.shortLabel || option.dataset.fullLabel) :
                            option.dataset.fullLabel;
                    });
                }

                function bindMortalityCommentOptionSelects() {
                    document.querySelectorAll('.js-mortality-comment-option-select').forEach((select) => {
                        if (select.dataset.bound === '1') {
                            collapseMortalityCommentOptionSelect(select);
                            return;
                        }

                        select.addEventListener('focus', () => expandMortalityCommentOptionSelect(select));
                        select.addEventListener('mousedown', () => expandMortalityCommentOptionSelect(select));
                        select.addEventListener('change', () => collapseMortalityCommentOptionSelect(select));
                        select.addEventListener('blur', () => collapseMortalityCommentOptionSelect(select));
                        select.dataset.bound = '1';
                        collapseMortalityCommentOptionSelect(select);
                    });
                }

                function normalizeStatus(value) {
                    return String(value || '').trim().toUpperCase();
                }

                function normalizeMode(value) {
                    return String(value || '').trim().toUpperCase();
                }

                function statusAllowsDetails(status) {
                    return ['A', 'OK'].includes(normalizeStatus(status));
                }

                function statusAllowsWoundedAndRotten(status) {
                    return statusAllowsDetails(status);
                }

                function statusAllowsMode(status) {
                    const normalized = normalizeStatus(status);
                    return normalized !== '' && !['OK', 'NF'].includes(normalized);
                }

                function modeContains(mode, letter) {
                    return normalizeMode(mode).includes(letter);
                }

                function normalizeDependentFields(record) {
                    const status = normalizeStatus(record.status);
                    const mode = normalizeMode(record.mode);

                    if (!statusAllowsMode(status)) {
                        record.mode = null;
                    }

                    if (status !== 'A') {
                        record.branches = null;
                    }

                    if (!statusAllowsDetails(status)) {
                        record.illumination = null;
                        record.deformity = null;
                        record.leaves = null;
                        record.leaf_damage = null;
                    }

                    if (!statusAllowsWoundedAndRotten(status)) {
                        record.wounded_stem = null;
                        record.rotten = null;
                    }

                    const normalizedMode = normalizeMode(record.mode);

                    if (!modeContains(normalizedMode, 'B')) {
                        record.living_length = null;
                    }

                    if (!modeContains(normalizedMode, 'U')) {
                        record.leaning = null;
                    }
                }

                function rowHasError(record) {
                    return !!record && errorRecordIds.includes(Number(record.id));
                }

                function commentsButtonRenderer(instance, td, row) {
                    Handsontable.renderers.TextRenderer.apply(this, arguments);
                    const source = instance.getSourceDataAtRow(row) || {};
                    td.innerHTML = '';
                    const wrapper = document.createElement('div');
                    wrapper.className = 'mortality-note-cell';
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.innerHTML = "<i class='fa-regular fa-note-sticky'></i>";
                    button.title = source.comments_json && source.comments_json.length ? '編輯備註' : '新增備註';
                    button.setAttribute('aria-label', button.title);
                    button.className = 'mortality-note-button';
                    button.style.width = '32px';
                    button.style.padding = '6px 8px';
                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        openCommentsEditor(source, button);
                    });

                    wrapper.appendChild(button);

                    if (source.comments_summary) {
                        const summary = document.createElement('span');
                        summary.className = 'mortality-note-summary';
                        summary.textContent = source.comments_summary;
                        summary.title = source.comments_summary;
                        wrapper.appendChild(summary);
                    }

                    td.appendChild(wrapper);
                    td.style.textAlign = 'left';
                    return td;
                }

                function openCommentsEditor(source, triggerElement = null) {
                    const componentId = findMortalityComponentId(triggerElement || document.getElementById(
                        'mortality-entry-root'));

                    if (!componentId || !window.Livewire || typeof window.Livewire.find !== 'function') {
                        return;
                    }

                    window.Livewire.find(componentId).call('openCommentsEditor', Number(source.id), currentRecords);
                }

                function normalizeRecords(records) {
                    return (records || []).map((record) => ({
                        ...record,
                        date: normalizeMortalityDate(record.date),
                        team_id: record.team_id === null || record.team_id === undefined ? '' : String(record.team_id),
                        comments_button: '',
                    }));
                }

                function getPaginatedRecords(records) {
                    const shouldPaginate = records.length > paginationThreshold;
                    const totalPages = shouldPaginate ? Math.ceil(records.length / pageSize) : 1;

                    if (currentHotPage > totalPages) {
                        currentHotPage = totalPages;
                    }
                    if (currentHotPage < 1) {
                        currentHotPage = 1;
                    }

                    if (!shouldPaginate) {
                        return {
                            shouldPaginate,
                            totalPages,
                            currentPage: 1,
                            startIndex: 0,
                            rows: records,
                        };
                    }

                    const start = (currentHotPage - 1) * pageSize;
                    const end = start + pageSize;

                    return {
                        shouldPaginate,
                        totalPages,
                        currentPage: currentHotPage,
                        startIndex: start,
                        rows: records.slice(start, end),
                    };
                }

                function updatePaginationUi(pagination) {
                    const note = document.getElementById('mortality-page-note');
                    const prev = document.getElementById('mortality-page-prev');
                    const next = document.getElementById('mortality-page-next');

                    if (!note || !prev || !next) {
                        return;
                    }

                    if (!pagination.shouldPaginate) {
                        note.textContent = '';
                        prev.style.visibility = 'hidden';
                        next.style.visibility = 'hidden';
                        return;
                    }

                    note.textContent = `第 ${pagination.currentPage} / ${pagination.totalPages} 頁`;
                    prev.style.visibility = pagination.currentPage > 1 ? 'visible' : 'hidden';
                    next.style.visibility = pagination.currentPage < pagination.totalPages ? 'visible' : 'hidden';
                }

                function renderVerticalHeader(column, TH) {
                    const label = colHeaders[column] || '';
                    TH.textContent = '';
                    TH.classList.remove('mortality-divider-right');
                    TH.classList.remove('mortality-divider-left');

                    const wrapper = document.createElement('div');
                    wrapper.className = 'mortality-hot-header';

                    const text = document.createElement('span');
                    text.className = 'mortality-hot-header-text';
                    text.innerHTML = label;

                    wrapper.appendChild(text);
                    TH.appendChild(wrapper);

                    if (column === 8) {
                        TH.classList.add('mortality-divider-right');
                    }
                    if ([13, 17].includes(column)) {
                        TH.classList.add('mortality-divider-left');
                    }
                    if (column === 13) {
                        TH.classList.add('mortality-divider-right');
                    }
                }

                function renderMortalityHot(retries = 0) {
                    const container = document.getElementById('mortality-entry-hot');

                    if (!container) {
                        if (mortalityHot) {
                            mortalityHot.destroy();
                            mortalityHot = null;
                        }
                        if (retries < 6 && currentEnabled) {
                            setTimeout(() => renderMortalityHot(retries + 1), 80);
                        }
                        return;
                    }

                    if (!currentEnabled) {
                        if (mortalityHot) {
                            mortalityHot.destroy();
                            mortalityHot = null;
                        }
                        return;
                    }

                    const records = normalizeRecords(currentRecords);
                    const pagination = getPaginatedRecords(records);
                    updatePaginationUi(pagination);

                    if (mortalityHot) {
                        mortalityHot.destroy();
                    }

                    mortalityHot = new Handsontable(container, {
                        licenseKey: 'non-commercial-and-evaluation',
                        data: pagination.rows,
                        columns,
                        colHeaders,
                        colWidths,
                        rowHeaders: true,
                        rowHeaderWidth: 38,
                        columnHeaderHeight: 138,
                        width: visibleTableWidth,
                        height: 'auto',
                        stretchH: 'none',
                        manualColumnResize: true,
                        manualRowResize: true,
                        fixedColumnsStart: 0,
                        hiddenColumns: {
                            columns: hiddenColumnIndexes,
                            indicators: false,
                        },
                        autoWrapRow: false,
                        autoWrapCol: false,
                        outsideClickDeselects: false,
                        contextMenu: false,
                        copyPaste: true,
                        filters: false,
                        dropdownMenu: false,
                        currentRowClassName: 'currentRow',
                        afterChange(changes, source) {
                            if (!changes || source === 'loadData') {
                                return;
                            }

                            changes.forEach(([row, prop, oldValue, newValue]) => {
                                const globalIndex = pagination.startIndex + row;
                                if (!currentRecords[globalIndex]) {
                                    return;
                                }
                                currentRecords[globalIndex][prop] = newValue;

                                if (prop === 'date') {
                                    currentRecords[globalIndex][prop] = normalizeMortalityDate(newValue);
                                }

                                if (prop === 'team_id') {
                                    currentRecords[globalIndex][prop] = teamIdFromLabel(newValue);
                                }

                                if (prop === 'status' || prop === 'mode') {
                                    normalizeDependentFields(currentRecords[globalIndex]);
                                }
                            });

                            mortalityHot.render();
                        },
                        afterGetColHeader(column, TH) {
                            renderVerticalHeader(column, TH);
                        },
                        afterGetRowHeader(row, TH) {
                            TH.classList.remove('mortality-error-row-header');
                            const sourceRecord = this.getSourceDataAtRow(row);

                            if (rowHasError(sourceRecord)) {
                                TH.classList.add('mortality-error-row-header');
                            }
                        },
                        cells(row, col) {
                            const props = {};
                            const sourceRecord = this.instance.getSourceDataAtRow(row) || {};
                            if (col === 0 || (col >= 3 && col <= 7) || col === 22) {
                                props.readOnly = true;
                            }
                            const classNames = [];
                            if (col === 8 || col === 13) {
                                classNames.push('mortality-divider-right');
                            }
                            if (col === 13 || col === 17) {
                                classNames.push('mortality-divider-left');
                            }
                            if (rowHasError(sourceRecord)) {
                                classNames.push('mortality-error-cell');
                            }
                            if (classNames.length > 0) {
                                props.className = classNames.join(' ');
                            }
                            return props;
                        },
                    });

                }

                function bindLivewireListener() {
                    if (livewireListenerBound || !window.Livewire || typeof window.Livewire.on !== 'function') {
                        return;
                    }

                    livewireListenerBound = true;

                    window.Livewire.on('mortality-entry-data', (payload) => {
                        const {
                            records,
                            enabled,
                            teamOptions
                        } = payload || {};
                        currentRecords = records || [];
                        currentEnabled = !!enabled;
                        currentTeamOptions = normalizeTeamOptions(teamOptions || []);
                        errorRecordIds = [];
                        currentHotPage = 1;
                        setTimeout(() => {
                            renderMortalityHot();
                        }, 0);
                    });

                    window.Livewire.on('mortality-entry-save-result', (payload) => {
                        errorRecordIds = (payload && payload.errorRecordIds ? payload.errorRecordIds : []).map((
                            id) => Number(id));
                        setTimeout(() => {
                            renderMortalityHot();
                        }, 0);
                    });

                    window.Livewire.on('mortality-entry-comment-saved', (payload) => {
                        const recordId = Number(payload && payload.recordId ? payload.recordId : 0);
                        errorRecordIds = (payload && payload.errorRecordIds ? payload.errorRecordIds : []).map((
                            id) => Number(id));

                        if (recordId > 0) {
                            currentRecords = currentRecords.map((record) => {
                                if (Number(record.id) !== recordId) {
                                    return record;
                                }

                                return {
                                    ...record,
                                    comments_json: payload.commentsJson || [],
                                    stem_corrections_json: payload.stemCorrectionsJson || [],
                                    comments_summary: payload.commentsSummary || '',
                                };
                            });
                        }

                        setTimeout(() => {
                            renderMortalityHot();
                        }, 0);
                    });

                }

                bindLivewireListener();
                document.addEventListener('livewire:init', bindLivewireListener);
                document.addEventListener('livewire:initialized', bindLivewireListener);

                window.mortalityPrevPage = function() {
                    currentHotPage = Math.max(currentHotPage - 1, 1);
                    renderMortalityHot();
                };

                window.mortalityNextPage = function() {
                    currentHotPage += 1;
                    renderMortalityHot();
                };

                window.mortalitySavePage = function() {
                    const componentId = findMortalityComponentId(document.getElementById('mortality-entry-hot-shell'));

                    if (!componentId || !window.Livewire || typeof window.Livewire.find !== 'function') {
                        return;
                    }

                    window.Livewire.find(componentId).call('saveEntryRecords', currentRecords);
                };

                window.mortalitySelectFallback = function(selectEl) {
                    const componentId = findMortalityComponentId(selectEl);

                    if (!selectEl || !componentId || !window.Livewire || typeof window.Livewire.find !== 'function') {
                        return;
                    }

                    window.Livewire.find(componentId).call('loadMapSort', selectEl.value);
                };

                document.addEventListener('DOMContentLoaded', () => {
                    bindLivewireListener();
                    bindMortalityCommentOptionSelects();
                });

                document.addEventListener('livewire:initialized', () => {
                    if (!window.Livewire || typeof window.Livewire.hook !== 'function') {
                        return;
                    }

                    window.Livewire.hook('message.processed', () => {
                        bindMortalityCommentOptionSelects();
                    });
                });
            })
            ();
        </script>
    @endonce
</div>
