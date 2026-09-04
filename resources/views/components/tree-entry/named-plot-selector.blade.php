@props([
    'options' => [],
    'model' => 'selectedPlot',
    'changeAction' => null,
    'previousAction' => null,
    'nextAction' => null,
    'message' => '',
    'label' => '選擇要輸入的樣區',
])

<div class="tree-entry-named-plot-selector" style="display:flex; align-items:center; gap:12px;">
    <span>{{ $label }}</span>
    <select class="fs100 entryplot" style="width:140px;"
        wire:model="{{ $model }}"
        @if ($changeAction) wire:change="{{ $changeAction }}" @endif>
        <option value=""></option>
        @foreach ($options as $value => $optionLabel)
            @php
                $optionValue = is_int($value) ? $optionLabel : $value;
            @endphp
            <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
        @endforeach
    </select>

    @if ($previousAction)
        <a class="a_" wire:click="{{ $previousAction }}">上一個樣方</a>
    @endif
    @if ($nextAction)
        <a class="a_" wire:click="{{ $nextAction }}">下一個樣方</a>
    @endif
    @if ($message !== '')
        <span>{{ $message }}</span>
    @endif
</div>
