@props([
    'qxOptions' => [],
    'qyOptions' => [],
    'qx',
    'qy',
    'submitAction' => 'submitForm',
    'previousAction' => null,
    'nextAction' => null,
    'message' => '',
    'label' => '選擇要輸入的樣方',
])

<div class="tree-entry-coordinate-selector">
    <div style="display:flex; align-items:center; gap:8px; white-space:nowrap;">
        <span style="margin-right:12px;">{{ $label }}</span>
        <form wire:submit.prevent="{{ $submitAction }}">
            <select class="fs100 entryqx" wire:model.live="qx" style="height:25px;">
                <option value="">qx</option>
                @foreach ($qxOptions as $value => $optionLabel)
                    @php
                        $optionValue = is_int($value) ? $optionLabel : $value;
                    @endphp
                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                @endforeach
            </select>
            -
            <select class="fs100 entryqy" wire:model.defer="qy" style="height:25px;">
                <option value="">qy</option>
                @foreach ($qyOptions as $value => $optionLabel)
                    @php
                        $optionValue = is_int($value) ? $optionLabel : $value;
                    @endphp
                    <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
                @endforeach
            </select>
            <button type="submit" style="margin-left:12px;">送出</button>
        </form>
        <span style="display:inline-block; min-width:92px; margin-left:22px; visibility:{{ $previousAction ? 'visible' : 'hidden' }};">
            <a class="a_" @if ($previousAction) wire:click="{{ $previousAction }}" @endif>上一個樣方</a>
        </span>
        <span style="display:inline-block; min-width:92px; visibility:{{ $nextAction ? 'visible' : 'hidden' }};">
            <a class="a_" @if ($nextAction) wire:click="{{ $nextAction }}" @endif>下一個樣方</a>
        </span>
        @if ($message !== '')
            <span style="padding-left:12px;">{{ $message }}</span>
        @endif
    </div>
</div>
