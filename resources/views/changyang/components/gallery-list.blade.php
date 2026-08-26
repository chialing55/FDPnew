<div class="page-content gallery-list">
    @if ($galleries->isNotEmpty())
        <div class="gallery-album-index" data-gallery-index>
            @foreach ($galleries as $gallery)
                @php $cover = $gallery->cover_image_path ?: $gallery->items->first()?->thumbnail_path ?: $gallery->items->first()?->image_path; @endphp
                <button class="gallery-album-card" type="button" data-open-album="gallery-album-{{ $gallery->id }}">
                    @if ($cover)
                        <span class="gallery-album-card__cover"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($cover) }}" alt="{{ $gallery->title }}" loading="lazy"></span>
                    @endif
                    <span class="gallery-album-card__details">
                        <strong>{{ $gallery->title }}</strong>
                        <small>{{ $gallery->items->count() }} photos</small>
                    </span>
                </button>
            @endforeach
        </div>

        @foreach ($galleries as $gallery)
        <section id="gallery-album-{{ $gallery->id }}" class="gallery-album" data-gallery-album hidden>
            <header>
                <button class="gallery-back" type="button" data-close-album>← Back to albums</button>
                <h2>{{ $gallery->title }}</h2>
                @if ($gallery->description)<p>{{ $gallery->description }}</p>@endif
            </header>
            <div class="gallery-grid">
                @foreach ($gallery->items as $item)
                    <figure class="gallery-item">
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" data-gallery-image data-alt="{{ $item->alt_text }}" data-caption="{{ $item->caption }}">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->thumbnail_path ?: $item->image_path) }}" alt="{{ $item->alt_text ?: '' }}" loading="lazy">
                        </a>
                        @if ($item->title || $item->caption)
                            <figcaption>{{ $item->title ?: $item->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        </section>
        @endforeach
    @else
        <p class="empty-state">No albums are currently available.</p>
    @endif
</div>

<dialog id="gallery-lightbox" class="gallery-lightbox" onclick="if (event.target === this) this.close()">
    <button type="button" aria-label="Close image" onclick="this.closest('dialog').close()">×</button>
    <figure><img src="" alt=""><figcaption></figcaption></figure>
</dialog>
