<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="fi-form-actions flex justify-end gap-3">
            <x-filament::button type="submit" icon="heroicon-o-check">Save changes</x-filament::button>
            <x-filament::button tag="a" :href="url('/cms')" color="gray">Cancel</x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
