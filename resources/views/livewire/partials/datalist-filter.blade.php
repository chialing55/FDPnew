@props([
    'model',
    'options' => [],
    'listId',
    'placeholder' => 'all',
    'width' => '90px',
    'change' => 'search',
])

<input
    type="text"
    class="fs100"
    style="width: {{ $width }};"
    list="{{ $listId }}"
    wire:model="{{ $model }}"
    wire:change="{{ $change }}"
    wire:keydown.enter="{{ $change }}"
    placeholder="{{ $placeholder }}"
>
<datalist id="{{ $listId }}">
    <option value="all"></option>
    @foreach($options as $option)
        @php
            $value = is_array($option) ? ($option['value'] ?? $option['label'] ?? '') : $option;
            $label = is_array($option) ? ($option['label'] ?? $value) : $option;
        @endphp
        @if((string) $value !== '')
            <option value="{{ $value }}">{{ $label }}</option>
        @endif
    @endforeach
</datalist>
