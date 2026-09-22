<div class="page-content">
    @forelse ($groups as $year => $items)
        <section class="content-section">
            <h2 class="content-section__title">{{ $year ?: 'Year not specified' }}</h2>
            <ol class="publication-list">
                @foreach ($items as $publication)
                    <li>
                        <p>
                            {!! str_replace('Chang-Yang, Chia-Hao', '<strong>Chang-Yang, Chia-Hao</strong>', e($publication->authors)) !!}.
                            <strong>{{ $publication->title }}</strong>.
                            @if ($publication->journal)<em>{{ $publication->journal }}</em>.@endif
                            @if ($publication->volume){{ $publication->volume }}@endif
                            @if ($publication->issue)({{ $publication->issue }})@endif
                            @if ($publication->pages): {{ $publication->pages }}.@endif
                            @if ($publication->type === 'preprint')<span>(Preprint)</span>@endif
                            @if ($publication->doi)
                                <a href="https://doi.org/{{ \App\Services\Web\PublicationIdentity::doi($publication->doi) }}" target="_blank" rel="noopener noreferrer">DOI</a>
                            @elseif ($publication->url && preg_match('~^https?://~i', $publication->url))
                                <a href="{{ $publication->url }}" target="_blank" rel="noopener noreferrer">View publication</a>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ol>
        </section>
    @empty
        <p class="empty-state">Publications are being reviewed.</p>
    @endforelse
</div>
