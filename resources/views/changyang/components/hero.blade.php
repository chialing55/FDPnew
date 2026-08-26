@if ($page->hero_image_path || $page->hero_title || $page->hero_subtitle)
    @php
        $heroSettings = $page->hero_settings ?? [];
        $heroStyle = collect([
            $page->hero_image_path ? "background-image: url('" . \Illuminate\Support\Facades\Storage::disk('public')->url($page->hero_image_path) . "')" : null,
            isset($heroSettings['position']) ? 'background-position: ' . $heroSettings['position'] : null,
            isset($heroSettings['height']) ? 'min-height: ' . $heroSettings['height'] : null,
        ])
            ->filter()
            ->implode('; ');
        $overlayOpacity = $heroSettings['overlay_opacity'] ?? 0.3;
    @endphp
    <section class="page-hero" style="{{ $heroStyle }}" aria-label="{{ $page->hero_image_alt ?: $page->title }}">
        <span class="page-hero__overlay" style="opacity: {{ $overlayOpacity }}"></span>
        <div class="page-hero__content">
            @if ($page->hero_title)
                <h1>{{ $page->hero_title }}</h1>
            @endif
            @if ($page->hero_subtitle)
                <h1 style='margin-top:10px;'>{{ $page->hero_subtitle }}</h1>
            @endif
        </div>
    </section>
@endif
