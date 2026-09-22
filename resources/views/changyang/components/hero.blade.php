@if ($page->hero_image_path || $page->hero_title || $page->hero_subtitle)
    @php
        $heroSettings = $page->hero_settings ?? [];
        $positionX = $heroSettings['position_x'] ?? null;
        $positionY = $heroSettings['position_y'] ?? null;
        $scale = $heroSettings['scale'] ?? null;
        $height = $heroSettings['frame_height'] ?? ($heroSettings['height'] ?? null);
        $heroStyle = collect([
            $positionX !== null && $positionY !== null ? "background-position: {$positionX}% {$positionY}%" : (isset($heroSettings['position']) ? 'background-position: ' . $heroSettings['position'] : null),
            $height !== null ? 'min-height: ' . (is_numeric($height) ? $height . 'px' : $height) : null,
        ])
            ->filter()
            ->implode('; ');
        $overlayOpacity = $heroSettings['overlay_opacity'] ?? 0.3;
    @endphp
    <section class="page-hero" style="{{ $heroStyle }}" aria-label="{{ $page->hero_image_alt ?: $page->title }}">
        @if ($page->hero_image_path)
            <span class="page-hero__image" style="background-image: url('{{ \Illuminate\Support\Facades\Storage::disk('public')->url($page->hero_image_path) }}'); transform: scale({{ max(1, min(3, (float) ($scale ?? 1))) }}); transform-origin: {{ $positionX ?? 50 }}% {{ $positionY ?? 50 }}%"></span>
        @endif
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
