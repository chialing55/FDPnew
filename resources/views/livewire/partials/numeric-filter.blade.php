@props([
    'operatorModel',
    'valueModel',
    'change' => 'search',
    'operatorWidth' => '40px',
    'valueWidth' => '40px',
    'step' => 'any',
])

<div style="display: inline-flex; gap: 4px; align-items: center;">
    <select class="fs100" style="width: {{ $operatorWidth }};" wire:model="{{ $operatorModel }}" wire:change="{{ $change }}">
        @foreach($operatorOptions as $operatorOption)
            <option value="{{ $operatorOption }}">{{ $operatorOption }}</option>
        @endforeach
    </select>
    <input type="number" step="{{ $step }}" class="fs100" style="width: {{ $valueWidth }};" wire:model="{{ $valueModel }}" wire:change="{{ $change }}" wire:keydown.enter="{{ $change }}">
</div>
