@php
    $previewImage = new \App\Models\ChangYang\BlockImage([
        'image_path' => '', 'preview_url' => $imageUrl,
        'alt_text' => data_get($preview, 'alt_text'),
        'caption' => data_get($preview, 'caption'),
        'photographer' => data_get($preview, 'photographer'),
        'display_settings' => $getState() ?? [],
    ]);
    $previewBlock = new \App\Models\ChangYang\ContentBlock([
        'heading' => data_get($preview, 'heading'),
        'content_html' => data_get($preview, 'content'),
        'layout' => $previewLayout,
    ]);
    $previewBlock->setRelation('images', collect($imageUrl ? [$previewImage] : []));
@endphp
@once
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;600;700&family=Lato:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/changyang.css') }}?v={{ filemtime(public_path('css/changyang.css')) }}">
@endonce
@if ($imageUrl && $previewLayout !== 'text_only')
    <p class="changyang-frame-editor__help">在照片內拖曳調整取樣位置，預覽使用前台版型。</p>
    <div class="changyang-frame-editor__controls" style="position: static; margin-bottom: 1.5rem; width: min(100%, 420px)">
        <label>顯示高度 <input type="range" min="{{ \App\Support\ChangYang\ImageFrame::MIN_HEIGHT }}" max="{{ \App\Support\ChangYang\ImageFrame::MAX_HEIGHT }}" step="10" x-model.number="state.frame_height"><output x-text="state.frame_height ? state.frame_height + 'px' : '原比例'"></output></label>
        <label>圖片放大 <input type="range" min="1" max="{{ \App\Support\ChangYang\ImageFrame::MAX_SCALE }}" step="0.05" x-model.number="state.scale"><output x-text="Math.round((state.scale || 1) * 100) + '%'"></output></label>
    </div>
@endif
<div class="changyang-block-preview">
    @include('changyang.components.page-blocks', [
        'blocks' => collect([$previewBlock]),
        'interactivePreview' => true,
    ])
</div>
<style>
    .changyang-block-preview .page-content { width: min(100%, 1106px) !important; margin: 0 auto !important; }
    .changyang-block-preview .content-section { margin-bottom: 0; }
    .changyang-block-preview figure[x-ref] { cursor: grab; touch-action: none; }
    .changyang-block-preview figure[x-ref] img { pointer-events: none; user-select: none; }
</style>
