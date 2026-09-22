@php
    $statePath = $getStatePath();
    $image = $getImagePath();
    $image = is_array($image) ? array_values($image)[0] ?? null : $image;
    $preview = $getPreviewData();
    $previewLayout = data_get($preview, 'layout', 'image_left');
    $showsTextPreview = $showsTextPreview();
    $imageUrl = match (true) {
        $image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile => $image->temporaryUrl(),
        is_string($image) && filled($image) => \Illuminate\Support\Facades\Storage::disk('public')->url($image),
        default => null,
    };
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="changyang-frame-editor"
        x-data="{
            state: @entangle($statePath),
            dragging: false,
            viewportWidth: document.documentElement.clientWidth,
            previewScale: 1,
            previewHeight: 0,
            resizeObserver: null,
            resizePreview() {
                if (! this.$refs.heroViewport || ! this.$refs.frame) return;
                this.viewportWidth = document.documentElement.clientWidth - (window.innerWidth <= 650 ? 28 : 0);
                this.previewScale = Math.min(1, this.$refs.heroViewport.clientWidth / this.viewportWidth);
                this.previewHeight = this.$refs.frame.offsetHeight * this.previewScale;
            },
            init() {
                this.$nextTick(() => {
                    if (! this.$refs.heroViewport) return;
                    this.resizeObserver = new ResizeObserver(() => this.resizePreview());
                    this.resizeObserver.observe(this.$refs.heroViewport);
                    this.resizeObserver.observe(this.$refs.frame);
                    this.resizePreview();
                });
            },
            destroy() { this.resizeObserver?.disconnect(); },
            normalize() {
                this.state = { ...@js(\App\Support\ChangYang\ImageFrame::normalize($getState())), ...(this.state || {}), object_fit: 'cover' };
            },
            move(event) {
                if (! this.dragging) return;
                const rect = this.$refs.frame.getBoundingClientRect();
                this.state.position_x = Math.min(100, Math.max(0, ((event.clientX - rect.left) / rect.width) * 100));
                this.state.position_y = Math.min(100, Math.max(0, ((event.clientY - rect.top) / rect.height) * 100));
            },
        }"
        x-init="normalize()"
        @resize.window="resizePreview()"
    >
        @if (data_get($preview, 'mode') === 'block')
            @include('forms.components.changyang-block-preview')
        @elseif (data_get($preview, 'mode') === 'hero' && $imageUrl)
            <p class="changyang-frame-editor__help">依目前視窗寬度，將前台首圖、文字與遮罩一起等比例縮小。可拖曳圖片或使用滑桿調整，儲存後套用到前台。</p>
            <div class="changyang-frame-editor__hero-controls">
                <label>顯示高度 <input type="range" min="{{ \App\Support\ChangYang\ImageFrame::MIN_HEIGHT }}" max="{{ \App\Support\ChangYang\ImageFrame::MAX_HEIGHT }}" step="10" x-model.number="state.frame_height"><output x-text="`${state.frame_height}px`"></output></label>
                <label>左右位置 <input type="range" min="0" max="100" step="1" x-model.number="state.position_x"><output x-text="`${Math.round(state.position_x)}%`"></output></label>
                <label>上下位置 <input type="range" min="0" max="100" step="1" x-model.number="state.position_y"><output x-text="`${Math.round(state.position_y)}%`"></output></label>
                <label>圖片放大 <input type="range" min="1" max="{{ \App\Support\ChangYang\ImageFrame::MAX_SCALE }}" step="0.05" x-model.number="state.scale"><output x-text="`${Math.round(state.scale * 100)}%`"></output></label>
            </div>
            <div class="changyang-block-preview changyang-frame-editor__hero-preview" x-ref="heroViewport"
                :style="`height: ${previewHeight}px`">
                <section class="page-hero" x-ref="frame"
                    :style="{ width: viewportWidth + 'px', transform: `scale(${previewScale})`, transformOrigin: 'top left', backgroundPosition: `${state.position_x}% ${state.position_y}%`, minHeight: `${state.frame_height}px` }"
                    @pointerdown.prevent="dragging = true; move($event)"
                    @pointermove="move($event)"
                    @pointerup.window="dragging = false"
                    @pointercancel.window="dragging = false">
                    <span class="page-hero__image" style="background-image: url('{{ $imageUrl }}')" :style="{ transform: `scale(${state.scale})`, transformOrigin: `${state.position_x}% ${state.position_y}%` }"></span>
                    <span class="page-hero__overlay" :style="{ opacity: state.overlay_opacity ?? 0.3 }"></span>
                    <div class="page-hero__content">
                        @if (filled(data_get($preview, 'heading')))<h1>{{ data_get($preview, 'heading') }}</h1>@endif
                        @if (filled(data_get($preview, 'subtitle')))<h1 style="margin-top:10px">{{ data_get($preview, 'subtitle') }}</h1>@endif
                    </div>
                </section>
            </div>
        @elseif ($imageUrl)
            <p class="changyang-frame-editor__help">在圖片內拖曳選擇取樣位置；使用滑桿放大。圖片寬度固定，調整高度可配合文字長度。</p>
            <div @class(['changyang-frame-editor__preview', 'changyang-frame-editor__preview--image-right' => $showsTextPreview && $previewLayout === 'image_right', 'changyang-frame-editor__preview--image-only' => ! $showsTextPreview])>
                <div class="changyang-frame-editor__controls">
                    <label>顯示高度 <input type="range" min="{{ \App\Support\ChangYang\ImageFrame::MIN_HEIGHT }}" max="{{ \App\Support\ChangYang\ImageFrame::MAX_HEIGHT }}" step="10" x-model.number="state.frame_height"><output x-text="`${state.frame_height}px`"></output></label>
                    <label>圖片放大 <input type="range" min="1" max="{{ \App\Support\ChangYang\ImageFrame::MAX_SCALE }}" step="0.05" x-model.number="state.scale"><output x-text="`${Math.round(state.scale * 100)}%`"></output></label>
                </div>
                <div class="changyang-frame-editor__media-preview">
                    <div class="changyang-frame-editor__frame" x-ref="frame"
                        :style="`height: ${state.frame_height}px`"
                        @pointerdown.prevent="dragging = true; move($event)"
                        @pointermove="move($event)"
                        @pointerup.window="dragging = false"
                        @pointercancel.window="dragging = false">
                        <img src="{{ $imageUrl }}" alt=""
                            :style="`object-position: ${state.position_x}% ${state.position_y}%; transform: scale(${state.scale}); transform-origin: ${state.position_x}% ${state.position_y}%`">
                    </div>
                    @if (filled(data_get($preview, 'photographer')))
                        <p style="margin-top: .5rem; font-size: .875rem; color: #625b54">攝影：{{ data_get($preview, 'photographer') }}</p>
                    @endif
                    @if (filled(data_get($preview, 'contact')))<div class="changyang-frame-editor__contact-preview web-content">{!! data_get($preview, 'contact') !!}</div>@endif
                </div>
                @if ($showsTextPreview)<div class="changyang-frame-editor__text-preview">
                    @if (filled(data_get($preview, 'heading')))<h3>{{ data_get($preview, 'heading') }}</h3>@endif
                    @if (filled(data_get($preview, 'content')))<div class="web-content">{!! data_get($preview, 'content') !!}</div>@else <p>主要內容會顯示在這裡。</p>@endif
                </div>
                @endif
            </div>
        @else
            <p class="changyang-frame-editor__empty">請先上傳或選擇圖片，才能調整取樣位置。</p>
        @endif
    </div>
</x-dynamic-component>

<style>
    .changyang-frame-editor { padding: 1rem; border: 1px solid #e3ded9; border-radius: .75rem; background: #faf8f6; }
    .changyang-frame-editor__help, .changyang-frame-editor__empty { margin: 0 0 .8rem; color: #625b54; font-size: .875rem; line-height: 1.5; }
    .changyang-frame-editor__preview { position: relative; display: grid; grid-template-columns: minmax(220px, 31%) minmax(0, 1fr); gap: 2.25rem; align-items: start; margin-top: 4.2rem; }
    .changyang-frame-editor__preview--image-right { grid-template-columns: minmax(0, 1fr) minmax(220px, 31%); }
    .changyang-frame-editor__preview--image-right .changyang-frame-editor__media-preview { order: 2; }
    .changyang-frame-editor__preview--image-only { display: block; width: min(100%, 26rem); }
    .changyang-frame-editor__preview--image-only .changyang-frame-editor__controls { width: 100%; }
    .changyang-frame-editor__media-preview { min-width: 0; }
    .changyang-frame-editor__frame { position: relative; width: 100%; overflow: hidden; border-radius: .5rem; background: #ded7d0; cursor: grab; touch-action: none; }
    .changyang-frame-editor__frame:active { cursor: grabbing; }
    .changyang-frame-editor__frame img { display: block; width: 100%; height: 100%; object-fit: cover; user-select: none; pointer-events: none; }
    .changyang-frame-editor__contact-preview { margin-top: 1rem; color: #272a2f; line-height: 1.55; }
    .changyang-frame-editor__text-preview { min-width: 0; color: #272a2f; line-height: 1.55; }
    .changyang-frame-editor__text-preview h3 { margin: .15rem 0 1rem; font-size: 1.25rem; font-weight: 700; }
    .changyang-frame-editor__text-preview p { margin: 0; color: #8b8179; }
    .changyang-frame-editor__controls { position: absolute; top: -3.7rem; left: 0; display: grid; gap: .65rem; width: 31%; min-width: 220px; }
    .changyang-frame-editor__controls label { display: grid; grid-template-columns: 5rem 1fr 3.5rem; align-items: center; gap: .65rem; font-size: .875rem; }
    .changyang-frame-editor__controls output { color: #625b54; font-variant-numeric: tabular-nums; text-align: right; }
    .changyang-frame-editor__hero-controls { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.75rem 1.5rem; margin-bottom:1rem; }
    .changyang-frame-editor__hero-controls label { display:grid; grid-template-columns:5rem 1fr 3.5rem; align-items:center; gap:.65rem; font-size:.875rem; }
    .changyang-frame-editor__hero-controls output { color:#625b54; font-variant-numeric:tabular-nums; text-align:right; }
    .changyang-frame-editor__hero-preview { padding:0; overflow:hidden; cursor:grab; touch-action:none; }
    .changyang-frame-editor__hero-preview:active { cursor:grabbing; }
    .changyang-frame-editor__hero-preview .page-hero { margin:0; }
    @media (max-width: 640px) { .changyang-frame-editor__preview, .changyang-frame-editor__preview--image-right { display: block; } .changyang-frame-editor__frame { margin-bottom: 1rem; } .changyang-frame-editor__controls { width: 100%; } .changyang-frame-editor__hero-controls { grid-template-columns:1fr; } }
</style>
