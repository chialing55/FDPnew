<div style="display: inline-flex; gap: 4px; align-items: center;">
    <select class="fs100" style="width: 40px;" wire:model="{{ $operatorModel }}" wire:change="search">
        @foreach($operatorOptions as $operatorOption)
            <option value="{{ $operatorOption }}">{{ $operatorOption }}</option>
        @endforeach
    </select>
    <input type="number" step="any" class="fs100" style="width: 40px;" wire:model="{{ $valueModel }}" wire:change="search">
</div>
