@props([
    'title' => null,
    'collapsible' => false,
    'open' => true,
])

<section {{ $attributes->class(['text_box', 'tree-entry-panel']) }}>
    @if ($title)
        <h6>{{ $title }}</h6>
        <hr>
    @endif

    @isset($reminders)
        <div class="simplenote" style="margin-top:10px; padding:10px 18px;">
            {{ $reminders }}
        </div>
    @endisset

    {{ $slot }}
</section>
