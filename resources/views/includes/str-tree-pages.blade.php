{{-- 選擇前後樣區 --}}

@php

    $prevshow = (($sqx.$sqy) == $plot2list[0]) ? 'prevhidden' : 'prevshow';
    $nextshow = (($sqx.$sqy) == end($plot2list)) ? 'prevhidden' : 'prevshow';

    $prev = ($prevshow == 'prevshow') ? $plot2list[($nowplotkey[0]-1)] : '00';
    $next = ($nextshow == 'prevshow') ? $plot2list[($nowplotkey[0]+1)] : '00';

@endphp

        <span class='{{$prevshow}}'><a class="a_" wire:click.once="searchSite({{$searchSiteVar}}, {{$prev[0]}},{{$prev[1]}})">上一個樣區</a></span>
        <span class='{{$nextshow}}'><a class="a_" wire:click.once="searchSite({{$searchSiteVar}}, {{$next[0]}},{{$next[1]}})">下一個樣區</a></span>
        