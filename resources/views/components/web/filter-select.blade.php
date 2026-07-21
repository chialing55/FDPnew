@props([
    'label',
    'placeholder',
    'options' => [],
])

<label class="block w-full text-sm font-medium text-gray-700">
    <span class="mb-1 block">{{ $label }}</span>
    <select {{ $attributes->class(['h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm']) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}">{{ $optionLabel }}</option>
        @endforeach
    </select>
</label>
