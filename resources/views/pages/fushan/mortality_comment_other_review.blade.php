@extends('layouts/mortality')

@section('pagejs')
    <script>
        $('.list3').addClass('now');
        $('.list3 hr').css('color', '#91A21C');

        function buildCommentOptionFullLabel(option) {
            const zh = option.comment_zh ? ` / ${option.comment_zh}` : '';
            const code = option.code ? ` (${option.code})` : '';
            return `${option.comment_en}${zh}${code}`;
        }

        function expandCommentOptionSelect(select) {
            Array.from(select.options).forEach((option) => {
                if (option.dataset.fullLabel) {
                    option.textContent = option.dataset.fullLabel;
                }
            });
        }

        function collapseCommentOptionSelect(select) {
            Array.from(select.options).forEach((option) => {
                if (!option.dataset.fullLabel) return;

                option.textContent = option.selected
                    ? (option.dataset.shortLabel || option.dataset.fullLabel)
                    : option.dataset.fullLabel;
            });
        }

        function bindCommentOptionSelect(select) {
            if (select.dataset.bound === '1') return;

            select.addEventListener('focus', function() {
                expandCommentOptionSelect(select);
            });

            select.addEventListener('mousedown', function() {
                expandCommentOptionSelect(select);
            });

            select.addEventListener('change', function() {
                collapseCommentOptionSelect(select);
            });

            select.addEventListener('blur', function() {
                collapseCommentOptionSelect(select);
            });

            select.dataset.bound = '1';
            collapseCommentOptionSelect(select);
        }

        function appendCommentOption(option) {
            document.querySelectorAll('.js-comment-option-select').forEach((select) => {
                const exists = Array.from(select.options).some((item) => item.value === String(option.id));
                if (exists) return;

                const element = document.createElement('option');
                element.value = option.id;
                element.dataset.fullLabel = buildCommentOptionFullLabel(option);
                element.dataset.shortLabel = option.comment_en || '';
                element.textContent = element.dataset.fullLabel;
                select.appendChild(element);
                collapseCommentOptionSelect(select);
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
                            throw new Error(data.message || '新增 option 失敗。');
                        }

                        appendCommentOption(data.option);
                        form.reset();

                        if (feedback) {
                            feedback.textContent = data.message;
                            feedback.style.display = 'block';
                            feedback.style.color = '#14532d';
                        }

                        closeCommentOptionModal();
                    } catch (error) {
                        if (feedback) {
                            feedback.textContent = error.message || '新增 option 失敗。';
                            feedback.style.display = 'block';
                            feedback.style.color = '#991b1b';
                        }
                    }
                });
            });

            document.querySelectorAll('.js-comment-option-select').forEach((select) => {
                bindCommentOptionSelect(select);
            });
        });
    </script>
@endsection

@section('rightbox')
    <div class='text_box' style="width:min(1280px, 96vw); text-align:left;">
        <h1>comment_other 整理資料表</h1>
        <hr>

        @if (session('status'))
            <div class="app-feedback-note app-feedback-note--success" style="margin:14px 0;">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="app-feedback-note app-feedback-note--error" style="margin:14px 0;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div style="margin:16px 0; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <button type="button" onclick="openCommentOptionModal()"
                style="padding:10px 18px; border:1px solid rgba(180,83,9,.35); border-radius:6px; background:#f59e0b; color:#fff; cursor:pointer;">
                新增 option
            </button>
            <a href="{{ route('admin.fushan.mortality.process') }}"
                style="display:inline-block; padding:10px 18px; border:1px solid rgba(0,0,0,.15); border-radius:6px; background:rgba(255,255,255,.9); color:#2f3e3b; text-decoration:none;">
                返回資料處理頁
            </a>
        </div>

        <div style="margin-bottom:16px; color:#475569;">
            目前待整理筆數：{{ $remainingCount }}，本頁顯示 {{ $records->count() }} 筆
        </div>

        <x-fushan.review-pagination :records="$records" margin="margin:0 0 18px 0;" />

        <form method="POST" action="{{ route('admin.fushan.mortality.process.comment-other.review.save') }}">
            @csrf
            <input type="hidden" name="page" value="{{ $records->currentPage() }}">

            <div style="margin:0 0 18px 0; display:flex; justify-content:flex-end;">
                <button type="submit"
                    style="padding:10px 18px; border:0; border-radius:6px; background:#3f5f5b; color:#fff; cursor:pointer;">
                    儲存本頁資料
                </button>
            </div>

            @foreach ($records as $record)
                <div class="review-card" style="padding:16px; border:1px solid rgba(0,0,0,.08); background:rgba(255,255,255,.78); border-radius:10px;">
                    <div style="display:grid; grid-template-columns:90px 120px 90px 160px minmax(240px, 320px) minmax(260px, 1fr) 100px; gap:16px; align-items:start;">
                        <div>
                            <div style="font-size:12px; color:#64748b;">儲存鍵</div>
                            <div style="font-weight:700;">{{ $record->id }}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#64748b;">census_record_id</div>
                            <div style="font-weight:700;">{{ $record->census_record_id ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#64748b;">Census</div>
                            <div>{{ $record->censusRecord->census ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#64748b;">StemID</div>
                            <div>{{ $record->censusRecord->stemid ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:12px; color:#64748b; margin-bottom:8px;">Comment Option</div>
                            <x-fushan.comment-option-select
                                name="records[{{ $record->id }}][comment_option_id]"
                                :options="$commentOptions"
                                :selected="$record->comment_option_id"
                                placeholder="不指定 option"
                                :show-short-when-selected="true"
                                style="width:100%; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;" />
                        </div>
                        <div>
                            <div style="font-size:12px; color:#64748b; margin-bottom:8px;">Comment Other</div>
                            <input type="text" name="records[{{ $record->id }}][comment_other]"
                                value="{{ $record->comment_other }}"
                                placeholder="原始 comment_other"
                                style="width:100%; height:40px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px; box-sizing:border-box;">
                        </div>
                        <div></div>
                    </div>
                </div>
            @endforeach

            <div style="margin:18px 0 0 0; display:flex; justify-content:flex-end;">
                <button type="submit"
                    style="padding:10px 18px; border:0; border-radius:6px; background:#3f5f5b; color:#fff; cursor:pointer;">
                    儲存本頁資料
                </button>
            </div>
        </form>

        <x-fushan.review-pagination :records="$records" />

        @include('partials.comment-option-manager', [
            'action' => route('admin.fushan.mortality.process.comments.options.store'),
            'categoryOptions' => $commentCategories ?? [],
        ])
    </div>
@endsection
