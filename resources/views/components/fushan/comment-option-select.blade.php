@props([
    'name',
    'options' => [],
    'selected' => '',
    'placeholder' => '選擇 option',
    'showShortWhenSelected' => false,
])

@php
    $selectedValue = (string) ($selected ?? '');
@endphp

<select name="{{ $name }}"
    {{ $attributes->merge([
        'class' => 'js-comment-option-select',
    ]) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $option)
        @php
            $optionValue = (string) $option->id;
            $isSelected = $selectedValue === $optionValue;
            $fullLabel = $option->comment_en . ($option->comment_zh ? ' / ' . $option->comment_zh : '') . ($option->code ? ' (' . $option->code . ')' : '');
            $shortLabel = $option->comment_en ?? '';
        @endphp
        <option value="{{ $option->id }}"
            data-full-label="{{ $fullLabel }}"
            data-short-label="{{ $shortLabel }}"
            {{ $isSelected ? 'selected' : '' }}>
            {{ $showShortWhenSelected && $isSelected ? $shortLabel : $fullLabel }}
        </option>
    @endforeach
</select>
