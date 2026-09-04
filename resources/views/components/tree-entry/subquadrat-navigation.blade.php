@props([
    'x',
    'y',
    'previousAction' => null,
    'nextAction' => null,
])

<div class="tree-entry-subquadrat-navigation" style="display:flex; align-items:center; margin:14px 0;">
    <h2 style="margin:0; min-width:180px;">({{ $x }}, {{ $y }})</h2>
    <span style="display:inline-block; min-width:125px; visibility:{{ $previousAction ? 'visible' : 'hidden' }};">
        <a class="a_" @if ($previousAction) wire:click="{{ $previousAction }}" @endif>上一個樣區</a>
    </span>
    <span style="display:inline-block; min-width:125px; visibility:{{ $nextAction ? 'visible' : 'hidden' }};">
        <a class="a_" @if ($nextAction) wire:click="{{ $nextAction }}" @endif>下一個樣區</a>
    </span>
</div>
