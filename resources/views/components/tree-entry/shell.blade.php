@props([
    'title',
    'entry',
    'user',
    'noteUrl' => null,
    'noteLabel' => '資料輸入注意事項',
    'inputDate' => null,
])

<div {{ $attributes->class(['tree-entry-shell']) }}>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>

    <h2>{{ $title }} 第 {{ $entry }} 次資料輸入</h2>

    <div style="margin-top:10px;">
        @if ($noteUrl)
            <p>請先詳閱 <a href="{{ $noteUrl }}"><b>{{ $noteLabel }}</b></a></p>
        @endif
        <p>輸入者 {{ $user }}，輸入日期 {{ $inputDate ?? date('Y-m-d') }}</p>
    </div>

    @isset($selector)
        <div class="tree-entry-selector" style="font-weight:800; margin-bottom:20px; display:flex; align-items:flex-start; gap:30px;">
            {{ $selector }}
        </div>
    @endisset

    {{ $slot }}
</div>
