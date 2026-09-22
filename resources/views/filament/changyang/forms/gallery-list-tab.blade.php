<div>
    <div style="display: flex; justify-content: flex-end; margin-bottom: 1rem">
        <x-filament::button tag="a" :href="\App\Filament\ChangYang\Resources\GalleryResource::getUrl('create')">新增相簿</x-filament::button>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1rem">
        @forelse ($albums as $album)
            @php($cover = $album->previewItem?->image_path)
            <a href="{{ \App\Filament\ChangYang\Resources\GalleryResource::getUrl('edit', ['record' => $album]) }}" style="display: block; border: 1px solid #e7e2dc; border-radius: .75rem; overflow: hidden">
                @if ($cover)
                    <img loading="lazy" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($cover) }}" alt="{{ $album->title }}" style="width: 100%; height: 170px; object-fit: cover">
                @else
                    <div style="height: 170px; display: grid; place-items: center; background: #f4f1ee">尚無照片</div>
                @endif
                <div style="padding: 1rem"><strong>{{ $album->title }}</strong><p>{{ $album->items_count }} 張照片 · {{ $album->is_active ? '公開' : '隱藏' }}</p><p>編輯相簿 →</p></div>
            </a>
        @empty
            <p>尚無相簿</p>
        @endforelse
    </div>
    @if ($albums->hasPages())
        <div class="mt-4">{{ $albums->appends(['tab' => '-gallery-tab'])->links() }}</div>
    @endif
</div>
