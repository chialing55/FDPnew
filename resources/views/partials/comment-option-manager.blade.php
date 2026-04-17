<div id="comment-option-modal"
    style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.35); z-index:9999; align-items:center; justify-content:center; padding:24px;">
    <div
        style="width:min(980px, 96vw); background:#fffdf7; border:1px solid rgba(217,119,6,.18); border-radius:12px; box-shadow:0 18px 40px rgba(15,23,42,.18); padding:18px;">
        <div style="display:flex; align-items:start; justify-content:space-between; gap:12px; margin-bottom:10px;">
            <div>
                <h2 style="margin-bottom:6px;">新增 Comment Option</h2>
                <p style="margin:0; color:#92400e;">新增前請先儲存已修改資料。</p>
            </div>
            <button type="button" onclick="closeCommentOptionModal()"
                style="border:0; background:transparent; font-size:24px; line-height:1; cursor:pointer; color:#64748b;">&times;</button>
        </div>

        <form method="POST"
            action="{{ $action }}"
            class="js-comment-option-manager"
            style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
            @csrf
            <div>
                <label style="display:block; margin-bottom:6px;">Code（可留空）</label>
                <input type="text" name="code" value="{{ old('code') }}"
                    placeholder="可不填"
                    style="width:180px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Comment EN</label>
                <input type="text" name="comment_en" value="{{ old('comment_en') }}"
                    style="width:280px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Comment ZH</label>
                <input type="text" name="comment_zh" value="{{ old('comment_zh') }}"
                    style="width:280px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px;">
            </div>
            <div>
                <label style="display:block; margin-bottom:6px;">Category（可選或輸入）</label>
                <input type="text" name="category" value="{{ old('category') }}"
                    list="comment-option-category-list"
                    placeholder="選擇既有分類或自行輸入"
                    style="width:160px; padding:8px 10px; border:1px solid #b6c2bf; border-radius:4px;">
            </div>
            <datalist id="comment-option-category-list">
                @foreach (($categoryOptions ?? []) as $categoryOption)
                    <option value="{{ $categoryOption }}"></option>
                @endforeach
            </datalist>
            <button type="submit"
                style="padding:10px 18px; border:0; border-radius:6px; background:#b45309; color:#fff; cursor:pointer;">
                儲存 option
            </button>
            <div class="js-comment-option-feedback" style="display:none; width:100%; margin-top:8px; color:#14532d;"></div>
        </form>
    </div>
</div>
