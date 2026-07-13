<x-filament-panels::page>
    <x-filament::section heading="上傳新 Hero 圖片" description="選擇圖片後會自動上傳並加入圖片庫。建議使用 1920 × 720 px 的寬幅橫式圖片。">
        <div class="space-y-5">
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-gray-950">選擇圖片</span>
                <input type="file" wire:model="heroUploads" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="cms-hero-file-input">
            </label>
            <div wire:loading wire:target="heroUploads" class="text-sm text-gray-500">正在上傳圖片…</div>
            @error('heroUploads') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
            @error('heroUploads.*') <p class="text-sm text-danger-600">{{ $message }}</p> @enderror
        </div>
    </x-filament::section>

    <x-filament::section heading="現有 Hero 圖片" description="首頁會從以下圖片中隨機顯示。">
        <div class="cms-hero-library">
            @forelse ($this->getHeroImages() as $image)
                <article class="cms-hero-library-item" wire:key="hero-{{ md5($image['name']) }}">
                    <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}">
                    <button
                        type="button"
                        class="cms-hero-delete"
                        title="刪除圖片"
                        wire:click="deleteHeroImage(@js($image['name']))"
                        wire:confirm="確定要刪除這張 Hero 圖片嗎？此動作無法復原。"
                    >
                        <x-heroicon-o-trash />
                    </button>
                    <div>{{ $image['name'] }}</div>
                </article>
            @empty
                <p class="text-sm text-gray-500">目前沒有 Hero 圖片。</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-panels::page>
