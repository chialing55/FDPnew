@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list3').addClass('now');
        $('.list3 hr').css('color', '#91A21C');

        function addCommentRow(button) {
            const container = button.closest('.review-card').querySelector('.comment-items');
            const sourceRow = container.querySelector('.comment-item-row:last-child');
            if (!sourceRow) return;

            const newRow = sourceRow.cloneNode(true);
            newRow.querySelectorAll('select, input').forEach((element) => {
                element.value = '';
            });
            container.appendChild(newRow);
            reindexCommentRows(button.closest('.review-card'));
        }

        function addStemCorrectionRow(button) {
            const container = button.closest('.review-card').querySelector('.stem-correction-items');
            const sourceRow = container.querySelector('.stem-correction-item-row:last-child');
            if (!sourceRow) return;

            const newRow = sourceRow.cloneNode(true);
            newRow.querySelectorAll('select, input').forEach((element) => {
                element.value = '';
            });
            container.appendChild(newRow);
            reindexStemCorrectionRows(button.closest('.review-card'));
        }

        function reindexCommentRows(reviewCard) {
            const recordId = reviewCard.dataset.recordId;
            const rows = reviewCard.querySelectorAll('.comment-item-row');

            rows.forEach((row, index) => {
                const elements = row.querySelectorAll('select, input');
                if (elements[0]) {
                    elements[0].name = `records[${recordId}][comment_items][${index}][comment_id]`;
                }
                if (elements[1]) {
                    elements[1].name = `records[${recordId}][comment_items][${index}][text]`;
                }
            });
        }

        function reindexStemCorrectionRows(reviewCard) {
            const recordId = reviewCard.dataset.recordId;
            const rows = reviewCard.querySelectorAll('.stem-correction-item-row');

            rows.forEach((row, index) => {
                const elements = row.querySelectorAll('select, input');
                if (elements[0]) {
                    elements[0].name = `records[${recordId}][stem_correction_items][${index}][field]`;
                }
                if (elements[1]) {
                    elements[1].name = `records[${recordId}][stem_correction_items][${index}][text]`;
                }
            });
        }

        function appendCommentOption(option) {
            document.querySelectorAll('.js-comment-option-select').forEach((select) => {
                const exists = Array.from(select.options).some((item) => item.value === String(option.id));
                if (exists) return;

                const element = document.createElement('option');
                element.value = option.id;
                const zh = option.comment_zh ? ` / ${option.comment_zh}` : '';
                const code = option.code ? ` (${option.code})` : '';
                element.textContent = `${option.comment_en}${zh}${code}`;
                select.appendChild(element);
            });
        }

        function openCommentOptionModal() {
            const modal = document.getElementById('comment-option-modal');
            if (modal) {
                modal.style.display = 'flex';
            }
        }

        function closeCommentOptionModal() {
            const modal = document.getElementById('comment-option-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('comment-option-modal');
            if (modal) {
                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        closeCommentOptionModal();
                    }
                });
            }

            document.querySelectorAll('.js-comment-option-manager').forEach((form) => {
                form.addEventListener('submit', async function(event) {
                    event.preventDefault();

                    const feedback = form.querySelector('.js-comment-option-feedback');
                    const formData = new FormData(form);

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            const message = data.message || '新增 option 失敗。';
                            throw new Error(message);
                        }

                        appendCommentOption(data.option);
                        form.reset();
                        if (feedback) {
                            feedback.textContent = data.message;
                            feedback.style.display = 'block';
                            feedback.style.color = '#14532d';
                        }
                        closeCommentOptionModal();
                        window.dispatchEvent(new CustomEvent('comment-option:created', {
                            detail: data.option
                        }));
                    } catch (error) {
                        if (feedback) {
                            feedback.textContent = error.message || '新增 option 失敗。';
                            feedback.style.display = 'block';
                            feedback.style.color = '#991b1b';
                        }
                    }
                });
            });

            document.querySelectorAll('.review-card').forEach((card) => {
                reindexCommentRows(card);
                reindexStemCorrectionRows(card);
            });
        });
    </script>
@endsection

@section('rightbox')
    <div class='text_box' style="width:min(1280px, 96vw); text-align:left;">
        <h1>Comments 整理資料表</h1>
        <hr>

        @if (session('status'))
            <div style="margin:14px 0; padding:10px 12px; border:1px solid rgba(34,197,94,.35); background:rgba(34,197,94,.12); color:#14532d; border-radius:6px;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin:14px 0; padding:10px 12px; border:1px solid rgba(220,38,38,.35); background:rgba(220,38,38,.08); color:#991b1b; border-radius:6px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div style="margin:16px 0; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <form method="POST" action="{{ route('admin.fushan.mortality.process.comments') }}" style="margin:0;">
                @csrf
                <input type="hidden" name="return_to" value="review">
                <button type="submit"
                    {{ empty($remainingCount) ? 'disabled' : '' }}
                    onclick="{{ empty($remainingCount) ? 'return false;' : "return confirm('確定要執行 comments 整理嗎？');" }}"
                    style="padding:10px 18px; border:0; border-radius:6px; background:{{ empty($remainingCount) ? '#d1d5db' : '#3f5f5b' }}; color:{{ empty($remainingCount) ? '#6b7280' : '#fff' }}; cursor:{{ empty($remainingCount) ? 'not-allowed' : 'pointer' }};">
                    執行 comments 整理
                </button>
            </form>

            @if (empty($remainingCount))
                <span style="color:#6b7280;">目前沒有待整理的 comments，按鈕已停用。</span>
            @endif
        </div>

        <div style="margin-bottom:16px; color:#475569;">
            目前待整理筆數：{{ $remainingCount }}，本次顯示前 {{ $records->count() }} 筆
        </div>

        <form method="POST" action="{{ route('admin.fushan.mortality.process.comments.review.save') }}">
            @csrf

            @foreach ($records as $record)
                @php
                    $existingComments = is_array($record->comments_json) ? $record->comments_json : [];
                    $existingStemCorrections = is_array($record->stem_corrections_json) ? $record->stem_corrections_json : [];
                    if (empty($existingComments)) {
                        $existingComments = [['comment_id' => '', 'text' => '']];
                    }
                    if (empty($existingStemCorrections)) {
                        $existingStemCorrections = [['field' => '', 'text' => '']];
                    }
                @endphp

                <div class="review-card" data-record-id="{{ $record->id }}" style="margin-bottom:18px; padding:16px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.78); border-radius:10px;">
                    <div style="display:grid; grid-template-columns:90px 190px 1fr 1fr; gap:16px; align-items:start;">
                        <div>
                            <div style="font-size:12px; color:#64748b;">StemID</div>
                            <div style="font-weight:700;">{{ $record->stemid ?: '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#64748b;">原始 comments</div>
                            <div>{{ $record->comments ?: '—' }}</div>
                        </div>

                        <div>
                            <div style="font-size:12px; color:#64748b; margin-bottom:8px;">Comments 整理</div>
                            <div class="comment-items" style="display:flex; flex-direction:column; gap:8px;">
                                @foreach ($existingComments as $itemIndex => $item)
                                    @php
                                        $selectedCommentId = $item['comment_id'] ?? '';
                                        if ($selectedCommentId === '' && !empty($item['code']) && isset($commentOptionIdsByCode[$item['code']])) {
                                            $selectedCommentId = $commentOptionIdsByCode[$item['code']];
                                        }
                                    @endphp
                                    <div class="comment-item-row" style="display:grid; grid-template-columns:220px minmax(180px, 1fr); gap:8px; align-items:center;">
                                        <x-fushan.comment-option-select
                                            name="records[{{ $record->id }}][comment_items][{{ $itemIndex }}][comment_id]"
                                            :options="$commentOptions"
                                            :selected="$selectedCommentId"
                                            placeholder="選擇 option"
                                            style="width:220px; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;" />
                                        <input type="text" name="records[{{ $record->id }}][comment_items][{{ $itemIndex }}][text]"
                                            value="{{ $item['text'] ?? '' }}"
                                            placeholder="note"
                                            style="width:100%; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;">
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" onclick="addCommentRow(this)"
                                style="margin-top:10px; padding:8px 14px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:#fff; cursor:pointer;">
                                新增一列
                            </button>
                            <button type="button" onclick="openCommentOptionModal()"
                                style="margin-top:10px; margin-left:8px; padding:8px 14px; border:1px solid rgba(180,83,9,.35); border-radius:6px; background:#f59e0b; color:#fff; cursor:pointer;">
                                新增 option
                            </button>
                        </div>

                        <div>
                            <div style="font-size:12px; color:#64748b; margin-bottom:8px;">stem_corrections</div>
                            <div class="stem-correction-items" style="display:flex; flex-direction:column; gap:8px;">
                                @foreach ($existingStemCorrections as $itemIndex => $item)
                                    <div class="stem-correction-item-row" style="display:grid; grid-template-columns:180px 112px; gap:8px; align-items:center; justify-content:start;">
                                        <select name="records[{{ $record->id }}][stem_correction_items][{{ $itemIndex }}][field]"
                                            style="width:180px; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;">
                                            <option value="">選擇欄位</option>
                                            @foreach ($stemCorrectionOptions as $fieldValue => $fieldLabel)
                                                <option value="{{ $fieldValue }}"
                                                    {{ ($item['field'] ?? '') === $fieldValue ? 'selected' : '' }}>
                                                    {{ $fieldLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="records[{{ $record->id }}][stem_correction_items][{{ $itemIndex }}][text]"
                                            value="{{ $item['text'] ?? '' }}"
                                            placeholder="text"
                                            style="width:112px; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;">
                                    </div>
                                @endforeach
                            </div>

                            <button type="button" onclick="addStemCorrectionRow(this)"
                                style="margin-top:10px; padding:8px 14px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:#fff; cursor:pointer;">
                                新增一列
                            </button>
                        </div>
                    </div>

                </div>
            @endforeach

            <div style="margin-top:18px; display:flex; justify-content:flex-end;">
                <button type="submit"
                    style="padding:10px 18px; border:0; border-radius:6px; background:#3f5f5b; color:#fff; cursor:pointer;">
                    儲存這批資料
                </button>
            </div>
        </form>

        @include('partials.comment-option-manager', [
            'action' => route('admin.fushan.mortality.process.comments.options.store'),
            'categoryOptions' => $commentCategories ?? [],
        ])
    </div>
@endsection
