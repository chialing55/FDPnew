@php
    $formatSectionHeading = static function (string $value): string {
        $withNewlines = preg_replace('#<br\s*/?>#i', "\n", $value) ?? $value;

        return nl2br(e($withNewlines), false);
    };
@endphp
<div class="page-content">
    @forelse ($blocks as $block)
        <section class="content-section">
            @if ($block->heading)
                <h2 class="content-section__title">{!! $formatSectionHeading($block->heading) !!}</h2>
            @endif
            <div class="content-section__blocks">
                    @php
                        $contentContainsImages = str_contains(strtolower($block->content_html ?? ''), '<img');
                        $hasStructuredMedia = in_array($block->layout, ['image_left', 'image_right'], true) && ! $contentContainsImages && $block->images->isNotEmpty();
                        $mediaWidth = data_get($block->images->first()?->display_settings, 'frame_width');
                        $mediaWidth = is_string($mediaWidth) && preg_match('/^\d+(?:\.\d+)?(?:px|rem|%)$/', $mediaWidth) ? $mediaWidth : null;
                    @endphp
                    <article @class(['content-block', 'content-block--'.$block->layout, 'has-structured-media' => $hasStructuredMedia]) @if($hasStructuredMedia && $mediaWidth) style="--media-width: {{ $mediaWidth }}" @endif>
                        @if ($hasStructuredMedia)
                            <div class="content-block__media">
                                @foreach ($block->images as $image)
                                    @php
                                        $imageSettings = \App\Support\ChangYang\ImageFrame::normalize($image->display_settings);
                                        $frameHeight = $imageSettings['frame_height'].'px';
                                        $objectFit = $imageSettings['object_fit'];
                                        $positionX = $imageSettings['position_x'].'%';
                                        $positionY = $imageSettings['position_y'].'%';
                                        $scale = $imageSettings['scale'];
                                    @endphp
                                    <figure @if ($interactivePreview ?? false)
                                        x-ref="frame" :class="{ 'has-crop': !!state.frame_height || state.scale > 1 }" :style="state.frame_height ? 'height: ' + state.frame_height + 'px' : ''"
                                        @pointerdown.prevent="dragging = true; move($event)" @pointermove="move($event)"
                                        @pointerup.window="dragging = false" @pointercancel.window="dragging = false"
                                        @endif
                                        @class(['has-crop' => $frameHeight || $scale > 1]) @if($frameHeight) style="height: {{ $frameHeight }}" @endif>
                                        @if ($image->link_url)<a href="{{ $image->link_url }}">@endif
                                        <img @if ($interactivePreview ?? false) :style="`object-fit: cover; object-position: ${state.position_x ?? 50}% ${state.position_y ?? 50}%; transform: scale(${state.scale || 1}); transform-origin: ${state.position_x ?? 50}% ${state.position_y ?? 50}%`" @endif src="{{ $image->preview_url ?? \Illuminate\Support\Facades\Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->alt_text ?: '' }}" style="object-fit: {{ $objectFit }}; object-position: {{ $positionX }} {{ $positionY }}; transform: scale({{ $scale }}); transform-origin: {{ $positionX }} {{ $positionY }}">
                                        @if ($image->link_url)</a>@endif

                                    </figure>
                                    @if ($image->photographer)<p style="margin-top: .5rem; font-size: .875rem; color: #625b54">攝影：{{ $image->photographer }}</p>@endif
                                @endforeach
                                @if ($block->media_content_html)
                                    <div class="content-block__media-content">{!! $block->media_content_html !!}</div>
                                @endif
                            </div>
                            <div class="content-block__body">
                                @if ($block->content_html)<div class="rich-text">{!! $block->content_html !!}</div>@endif
                            </div>
                        @else
                            @if (! $contentContainsImages && $block->images->isNotEmpty())
                                <div class="content-block__images">
                                    @foreach ($block->images as $image)
                                        <figure>
                                            @if ($image->link_url)<a href="{{ $image->link_url }}">@endif
                                            <img @if ($interactivePreview ?? false) :style="`object-fit: cover; object-position: ${state.position_x ?? 50}% ${state.position_y ?? 50}%; transform: scale(${state.scale || 1}); transform-origin: ${state.position_x ?? 50}% ${state.position_y ?? 50}%`" @endif src="{{ $image->preview_url ?? \Illuminate\Support\Facades\Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->alt_text ?: '' }}">
                                            @if ($image->link_url)</a>@endif

                                        </figure>
                                    @if ($image->photographer)<p style="margin-top: .5rem; font-size: .875rem; color: #625b54">攝影：{{ $image->photographer }}</p>@endif
                                    @endforeach
                                </div>
                            @endif
                            @if ($block->content_html)<div class="rich-text">{!! $block->content_html !!}</div>@endif
                        @endif
                    </article>
            </div>
        </section>
    @empty
        @if ($currentPage->template !== 'publications')
            <p class="empty-state">This page is being prepared.</p>
        @endif
    @endforelse
</div>
