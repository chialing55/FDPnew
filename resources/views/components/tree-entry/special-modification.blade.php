@props(['id'])

<div id="{{ $id }}" class="tree-entry-special-dialog" style="display:none;" role="dialog" aria-modal="true">
    <div class="tree-entry-special-dialog-panel">
        <div class="tree-entry-special-heading">
            <h6 id="{{ $id }}-title">特殊修改</h6>
            <span id="{{ $id }}-note">*只需填寫需修改的資料</span>
        </div>
        <p>stemid：<span id="{{ $id }}-stemid"></span></p>
        <div id="{{ $id }}-hot"></div>
        <div id="{{ $id }}-feedback" class="savenote" style="margin-top:8px;"></div>
        <p class="tree-entry-special-actions">
            <button id="{{ $id }}-save" type="button" class="datasavebutton">儲存特殊修改</button>
            <button type="button" onclick="window.TreeEntryGrid.closeSpecialModification('{{ $id }}')">關閉視窗</button>
        </p>
    </div>
</div>
