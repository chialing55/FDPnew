@php
    $imageRows = collect($images)->values();
    $firstImage = $imageRows->first() ?? [];
    $firstImagePath = data_get($firstImage, 'image_path');
    $firstImagePath = is_array($firstImagePath) ? array_values($firstImagePath)[0] ?? null : $firstImagePath;
    $hasImage = is_string($firstImagePath) && filled($firstImagePath);
    $hasMedia = in_array($layout, ['image_left', 'image_right'], true) && $hasImage;
    $settings = data_get($firstImage, 'display_settings', []);
    $heightValue = data_get($settings, 'frame_height');
    $height = filled($heightValue) ? (str_ends_with((string) $heightValue, 'px') ? $heightValue : $heightValue.'px') : null;
    $positionXValue = data_get($settings, 'position_x', 50);
    $positionYValue = data_get($settings, 'position_y', 50);
    $positionX = str_ends_with((string) $positionXValue, '%') ? $positionXValue : $positionXValue.'%';
    $positionY = str_ends_with((string) $positionYValue, '%') ? $positionYValue : $positionYValue.'%';
    $fit = data_get($settings, 'object_fit', 'cover');
    $scale = min(3, max(1, (float) data_get($settings, 'scale', 1)));
@endphp

<div class="changyang-block-preview">
    <div class="changyang-block-preview__label">內容區塊預覽</div>
    <article @class(['changyang-block-preview__content', 'changyang-block-preview__content--with-media' => $hasMedia, 'changyang-block-preview__content--image-right' => $layout === 'image_right'])>
        @if ($hasMedia)
            <div class="changyang-block-preview__media">
                <div class="changyang-block-preview__image-frame" @if ($height) style="height: {{ $height }}" @endif>
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($firstImagePath) }}"
                        alt="{{ $firstImage['alt_text'] ?? '' }}"
                        style="object-fit: {{ $fit }}; object-position: {{ $positionX }} {{ $positionY }}; transform: scale({{ $scale }}); transform-origin: {{ $positionX }} {{ $positionY }}">
                </div>
                @if (filled($mediaContent))<div class="changyang-block-preview__media-text">{!! $mediaContent !!}</div>@endif
            </div>
        @endif
        <div class="changyang-block-preview__body">
            @if (filled($heading))<h3>{{ $heading }}</h3>@endif
            @if (! $hasMedia && $hasImage)
                <div class="changyang-block-preview__images">
                    @foreach ($images as $image)
                        @php
                            $imagePath = data_get($image, 'image_path');
                            $imagePath = is_array($imagePath) ? array_values($imagePath)[0] ?? null : $imagePath;
                        @endphp
                        @if (is_string($imagePath) && filled($imagePath))
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath) }}" alt="{{ $image['alt_text'] ?? '' }}">
                        @endif
                    @endforeach
                </div>
            @endif
            @if (filled($content))<div class="web-content">{!! $content !!}</div>@else <p class="changyang-block-preview__empty">請輸入主要內容以預覽。</p>@endif
        </div>
    </article>
</div>

<style>
    .changyang-block-preview { padding: 1rem; border: 1px solid #e3ded9; border-radius: .75rem; background: #faf8f6; }
    .changyang-block-preview__label { margin-bottom: .75rem; color: #625b54; font-size: .85rem; font-weight: 700; }
    .changyang-block-preview__content { padding: 1rem; border: 1px solid #eee7e1; border-radius: .5rem; background: #fff; }
    .changyang-block-preview__content--with-media { display: flex; gap: 1rem; align-items: flex-start; }
    .changyang-block-preview__content--image-right .changyang-block-preview__media { order: 2; }
    .changyang-block-preview__media { flex: 0 0 min(38%, 20rem); }
    .changyang-block-preview__image-frame { overflow: hidden; border-radius: .4rem; }
    .changyang-block-preview__media img { display: block; width: 100%; height: 100%; min-height: 12rem; max-height: 32rem; border-radius: .4rem; }
    .changyang-block-preview__media-text { margin-top: .65rem; }
    .changyang-block-preview__images { display: grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .changyang-block-preview__images img { width: 100%; border-radius: .4rem; }
    .changyang-block-preview__body { min-width: 0; flex: 1; }
    .changyang-block-preview__body h3 { margin: 0 0 .65rem; font-size: 1.1rem; font-weight: 700; }
    .changyang-block-preview__empty { margin: 0; color: #8b8179; }
    @media (max-width: 640px) { .changyang-block-preview__content--with-media { display: block; } .changyang-block-preview__media { margin-bottom: 1rem; } }
</style>
