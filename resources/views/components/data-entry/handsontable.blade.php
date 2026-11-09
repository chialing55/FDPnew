@props([
    'id',
    'saveLabel' => '儲存此 Plot',
])

<div class="data-entry-grid-wrap">
    <span id="{{ $id }}-feedback-top" class="app-feedback-note"></span>
    <div id="{{ $id }}" wire:ignore class="data-entry-hot"></div>
    <span id="{{ $id }}-feedback" class="app-feedback-note"></span>
    <p class="data-entry-save-row">
        <button id="{{ $id }}-save" type="button" class="datasavebutton">{{ $saveLabel }}</button>
    </p>
</div>
