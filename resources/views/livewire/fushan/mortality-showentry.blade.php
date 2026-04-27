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
                @elseif ($suggestedMapKey !== null)
                    <span style="color:#475569;">建議從目前第一個未完成的 map 開始。</span>
                @endif
            </div>
        @else
            <div style="margin-top:14px; color:#475569;">
                目前尚無可輸入資料，請先建立 `record1` / `record2`。
            </div>
        @endif

    </div>

    @if ($selectedMapSort !== null && $selectedMap !== null)
        <div class='text_box'>
            <form wire:submit.prevent="saveSurveyMeta"
                style="display:flex; flex-direction:column; gap:12px; margin-bottom:14px; color:#334155; line-height:1.8;">
                <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                    <span style="font-weight:700; min-width:72px;">調查日期</span>
                    <input type="date" wire:model="surveyDate"
                        style="width:140px; height:34px; padding:4px 8px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">
                </div>

                <div style="display:flex; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                    <span style="font-weight:700; min-width:72px; padding-top:4px;">調查人員</span>
                    <select wire:model="selectedTeamId"
                        style="min-width:280px; height:34px; padding:4px 8px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box; background:#fff;">
                        <option value="">本次調查團隊（可不選）</option>
                        @foreach ($teamOptions as $teamOption)
                            <option value="{{ $teamOption['id'] }}">{{ $teamOption['label'] }}</option>
                        @endforeach
                    </select>

                    <div style="display:flex; align-items:flex-start; gap:8px; flex-wrap:wrap; flex:1 1 700px;">
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
                                儲存調查日期與人員
                            </button>
                        </span>
                    </div>

                </div>
                <div>
                    <p style='font-size:14px; color:#64748b; margin:0;'>* 可選調查團隊或自行新增團隊 - 填入調查人員組合即會自動新增團隊。</p>
                </div>
                @error('surveyDate')
                    <div style="color:#b91c1c;">{{ $message }}</div>
                @enderror
                @error('surveyPersonnel')
                    <div style="color:#b91c1c;">{{ $message }}</div>
                @enderror
                @if (!empty($surveyMetaMessage))
                    <div style="color:#166534;">{{ $surveyMetaMessage }}</div>
                @endif
            </form>
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

                const columns = [{
                        data: 'map',
                        readOnly: true
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
                        validator: integerRangeValidator(1, 100)
                    },
                    {
                        data: 'illumination',
                        type: 'dropdown',
                        source: ['', '1', '2', '3', '4', '5'],
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

                const colWidths = [44, 54, 54, 86, 120, 60, 60, 58, 48, 58, 52, 52, 48, 54, 42, 54, 58, 52, 48, 54, 200];
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
                    return ['A', 'OK', 'D'].includes(normalizeStatus(status));
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

                    if (column === 6) {
                        TH.classList.add('mortality-divider-right');
                    }
                    if ([11, 15].includes(column)) {
                        TH.classList.add('mortality-divider-left');
                    }
                    if (column === 11) {
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
                            if (col <= 5 || col === 20) {
                                props.readOnly = true;
                            }
                            const classNames = [];
                            if (col === 6 || col === 11) {
                                classNames.push('mortality-divider-right');
                            }
                            if (col === 11 || col === 15) {
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
                            enabled
                        } = payload || {};
                        currentRecords = records || [];
                        currentEnabled = !!enabled;
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
