@props([
    'columns' => 4,
    'rows' => 4,
    'selectedX' => 1,
    'selectedY' => 1,
    'action' => 'selectSubquadrat',
    'label' => '選擇小樣區',
])

<div class="tree-entry-subquadrat-selector" style="display:flex; gap:16px; padding-left:20px; border-left:1px solid #777;">
    <span>{{ $label }}</span>
    <div style="line-height:1.5;">
        @for ($y = $rows; $y >= 1; $y--)
            @for ($x = 1; $x <= $columns; $x++)
                <button type="button"
                    class="plottable2 plot{{ $x }}{{ $y }} {{ (int) $selectedX === $x && (int) $selectedY === $y ? 'selected' : '' }}"
                    style="border-radius:0; box-sizing:border-box;"
                    wire:click="{{ $action }}({{ $x }}, {{ $y }})">{{ $x }}, {{ $y }}</button>
            @endfor
            <br>
        @endfor
    </div>
</div>
