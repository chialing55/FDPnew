@props(['field', 'label'])

<button type="button" class="sortable-heading" wire:click="sortBy('{{ $field }}')">
    {{ $label }}{{ $this->sortIndicator($field) }}
</button>
