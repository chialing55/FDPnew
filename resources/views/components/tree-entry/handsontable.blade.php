@props([
    'id',
    'saveEnabled' => false,
    'saveLabel' => '儲存',
])

<div class="tree-entry-data-table" style="display:inline-flex; flex-direction:column; margin-top:20px; max-width:100%;">
    <div class="pages" style="margin-bottom:14px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <div id="{{ $id }}-total" class="totalnum"></div>
        <div id="{{ $id }}-page" class="pagenote" style="display:none;"></div>
        <label style="display:inline-flex; align-items:center; gap:6px;">
            <span>每頁</span>
            <select id="{{ $id }}-size">
                <option value="20">20</option>
                <option value="40">40</option>
            </select>
            <span>筆</span>
        </label>
        <a id="{{ $id }}-previous" class="prev" style="display:none;">上一頁</a>
        <a id="{{ $id }}-next" class="next" style="display:none;">下一頁</a>
    </div>

    <span id="{{ $id }}-feedback-top" class="datasavenote savenote app-feedback-note"></span>
    <div id="{{ $id }}" wire:ignore class="fs100"></div>
    <span id="{{ $id }}-feedback" class="datasavenote savenote app-feedback-note"></span>
    <p style="margin-top:15px; text-align:center;">
        <button id="{{ $id }}-save" type="button" class="datasavebutton" @disabled(!$saveEnabled)>{{ $saveLabel }}</button>
    </p>
</div>
