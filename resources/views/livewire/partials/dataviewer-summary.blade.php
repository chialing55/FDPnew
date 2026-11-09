@props([
    'title',
    'latestLabel' => null,
    'latestValue' => null,
    'total' => 0,
])

<h2>
    {{ $title }}
    @if($latestLabel)
        <span style="margin-left: 20px; font-weight: 500; font-size: 70%;">{{ $latestLabel }}：{{ $latestValue }}</span>
    @endif
    <span style="margin-left: 20px; font-weight: 500; font-size: 70%;">共 {{ number_format($total) }} 筆</span>
</h2>
