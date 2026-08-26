@php
    $formatSectionHeading = static function (string $value): string {
        $withNewlines = preg_replace('#<br\s*/?>#i', "\n", $value) ?? $value;

        return nl2br(e($withNewlines), false);
    };
@endphp
<div class="page-content">
    @forelse ($sections as $section)
        <section class="content-section" @if(data_get($section->settings, 'background_color')) style="background-color: {{ data_get($section->settings, 'background_color') }}" @endif>
            @if ($section->heading)
                <h2 class="content-section__title">{!! $formatSectionHeading($section->heading) !!}</h2>
            @endif
            @if ($section->subheading)
                <p class="content-section__subtitle">{{ $section->subheading }}</p>
            @endif

            <div class="content-section__blocks">
                @foreach ($section->blocks as $block)
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
                                        $imageSettings = $image->display_settings ?? [];
                                        $frameHeight = data_get($imageSettings, 'frame_height');
                                        $frameHeight = is_string($frameHeight) && preg_match('/^\d+(?:\.\d+)?(?:px|rem|vh)$/', $frameHeight) ? $frameHeight : null;
                                        $objectFit = data_get($imageSettings, 'object_fit');
                                        $objectFit = in_array($objectFit, ['cover', 'contain', 'fill', 'scale-down'], true) ? $objectFit : 'cover';
                                        $positionX = data_get($imageSettings, 'position_x');
                                        $positionX = is_string($positionX) && preg_match('/^\d+(?:\.\d+)?%$/', $positionX) ? $positionX : '50%';
                                        $positionY = data_get($imageSettings, 'position_y');
                                        $positionY = is_string($positionY) && preg_match('/^\d+(?:\.\d+)?%$/', $positionY) ? $positionY : '50%';
                                    @endphp
                                    <figure @class(['has-crop' => $frameHeight]) @if($frameHeight) style="height: {{ $frameHeight }}" @endif>
                                        @if ($image->link_url)<a href="{{ $image->link_url }}">@endif
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->alt_text ?: '' }}" style="object-fit: {{ $objectFit }}; object-position: {{ $positionX }} {{ $positionY }}">
                                        @if ($image->link_url)</a>@endif
                                        @if ($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif
                                    </figure>
                                @endforeach
                                @if ($block->media_content_html)
                                    <div class="content-block__media-content">{!! $block->media_content_html !!}</div>
                                @endif
                            </div>
                            <div class="content-block__body">
                                @if ($block->heading)<h3>{{ $block->heading }}</h3>@endif
                                @if ($block->content_html)<div class="rich-text">{!! $block->content_html !!}</div>@endif
                            </div>
                        @else
                            @if ($block->heading)<h3>{{ $block->heading }}</h3>@endif
                            @if (! $contentContainsImages && $block->images->isNotEmpty())
                                <div class="content-block__images">
                                    @foreach ($block->images as $image)
                                        <figure>
                                            @if ($image->link_url)<a href="{{ $image->link_url }}">@endif
                                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->alt_text ?: '' }}">
                                            @if ($image->link_url)</a>@endif
                                            @if ($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif
                                        </figure>
                                    @endforeach
                                </div>
                            @endif
                            @if ($block->content_html)<div class="rich-text">{!! $block->content_html !!}</div>@endif
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        @if ($currentPage->template !== 'publications')
            <p class="empty-state">This page is being prepared.</p>
        @endif
    @endforelse
</div>
