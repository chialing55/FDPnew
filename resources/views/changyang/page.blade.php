<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $currentPage->meta_description ?: 'Plant Ecology Lab at National Sun Yat-sen University' }}">
    <title>{{ $currentPage->slug === 'home' ? 'Plant Ecology Lab at NSYSU' : $currentPage->title.' | Plant Ecology Lab at NSYSU' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;600;700&family=Lato:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/changyang.css') }}?v={{ filemtime(public_path('css/changyang.css')) }}">
</head>
<body class="changyang-site page-{{ $currentPage->slug }}">
    <header class="site-header">
        <div class="site-header__inner">
            <a class="site-title" href="{{ route('changyang.home') }}">Plant Ecology Lab at NSYSU</a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">
                <span class="sr-only">Toggle navigation</span>
                <span></span><span></span><span></span>
            </button>
            <nav id="site-navigation" class="site-navigation" aria-label="Primary navigation">
                @foreach ($navigation as $navPage)
                    <a href="{{ $navPage->slug === 'home' ? route('changyang.home') : route('changyang.page', ['page' => $navPage->slug]) }}"
                       @class(['is-active' => $currentPage->is($navPage)])>{{ $navPage->navigation_label ?: $navPage->title }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <main id="main-content" class="site-main">
        @include('changyang.components.hero', ['page' => $currentPage])

        @if ($currentPage->template === 'news')
            @include('changyang.components.news-list', ['groups' => $newsGroups])
        @elseif ($currentPage->template === 'gallery')
            @include('changyang.components.gallery-list', ['galleries' => $galleries])
        @else
            @include('changyang.components.page-sections', ['sections' => $currentPage->sections])
        @endif
    </main>

    <footer class="site-footer">
        <p>Plant Ecology Lab · National Sun Yat-sen University</p>
    </footer>

    <script>
        const toggle = document.querySelector('.nav-toggle');
        const navigation = document.querySelector('.site-navigation');
        toggle?.addEventListener('click', () => {
            const open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!open));
            navigation.classList.toggle('is-open', !open);
        });

        document.querySelectorAll('[data-gallery-image]').forEach((link) => {
            link.addEventListener('click', (event) => {
                event.preventDefault();
                const dialog = document.querySelector('#gallery-lightbox');
                dialog.querySelector('img').src = link.href;
                dialog.querySelector('img').alt = link.dataset.alt || '';
                dialog.querySelector('figcaption').textContent = link.dataset.caption || '';
                dialog.showModal();
            });
        });

        const albumIndex = document.querySelector('[data-gallery-index]');
        const albums = document.querySelectorAll('[data-gallery-album]');
        document.querySelectorAll('[data-open-album]').forEach((button) => {
            button.addEventListener('click', () => {
                const album = document.getElementById(button.dataset.openAlbum);
                if (!album) return;
                albumIndex.hidden = true;
                albums.forEach((item) => item.hidden = item !== album);
                album.hidden = false;
                album.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
        document.querySelectorAll('[data-close-album]').forEach((button) => {
            button.addEventListener('click', () => {
                albums.forEach((album) => album.hidden = true);
                albumIndex.hidden = false;
                albumIndex.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    </script>
</body>
</html>
