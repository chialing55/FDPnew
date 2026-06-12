@props([
    'model',
    'options' => [],
    'change' => 'search',
    'width' => null,
    'allLabel' => 'all',
])

<select class="fs100" @if($width) style="width: {{ $width }};" @endif wire:model="{{ $model }}" wire:change="{{ $change }}">
    <option value="all">{{ $allLabel }}</option>
    @foreach($options as $option)
        @php
            $value = is_array($option) ? ($option['value'] ?? $option['spcode'] ?? $option['label'] ?? '') : $option;
            $label = is_array($option) ? ($option['label'] ?? $value) : $option;
        @endphp
        @if((string) $value !== '')
            <option value="{{ $value }}">{{ $label }}</option>
        @endif
    @endforeach
</select>
