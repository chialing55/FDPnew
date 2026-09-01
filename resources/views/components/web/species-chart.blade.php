@props([
    'title',
    'chart',
    'canvasId',
    'groupTitle' => null,
    'description' => null,
    'map' => false,
])

<div class="species-chart-item">
    @if ($groupTitle)
        <h5 class="species-chart-group-title">{{ $groupTitle }}</h5>
    @endif

    @if ($description)
        <div class="mt-2">
            <p>{{ $description }}</p>
        </div>
    @endif

    <h6 class="species-chart-title">{{ $title }}</h6>
    <div @class([
        'fig',
        $chart,
        'species-chart-frame',
        'species-chart-map-frame' => $map,
    ]) wire:ignore>
        <div class="species-chart-placeholder">圖表載入中...</div>
        <canvas id="{{ $canvasId }}"></canvas>
    </div>
</div>
