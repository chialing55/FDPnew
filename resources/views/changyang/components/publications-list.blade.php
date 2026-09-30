<div class="page-content">
    @forelse ($groups as $year => $items)
        <section class="content-section">
            <h2 class="content-section__title">{{ $year ?: 'Year not specified' }}</h2>
            <ol class="publication-list">
                @foreach ($items as $publication)
                    <li>
                        <p>
                            {!! $publication->chang_yang_citation_html !!}
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
