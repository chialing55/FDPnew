@php
    $monthNames = [1 => 'Jan.', 2 => 'Feb.', 3 => 'Mar.', 4 => 'Apr.', 5 => 'May', 6 => 'Jun.', 7 => 'Jul.', 8 => 'Aug.', 9 => 'Sep.', 10 => 'Oct.', 11 => 'Nov.', 12 => 'Dec.'];
@endphp
<div class="page-content news-list">
    @forelse ($groups as $key => $items)
        @php $first = $items->first(); @endphp
        <section class="news-group">
            <h2>{{ $monthNames[$first->category_month] }} {{ $first->category_year }}</h2>
            <ul>
                @foreach ($items as $item)
                    <li>{!! $item->content_html !!}</li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="empty-state">No news is currently available.</p>
    @endforelse
</div>
