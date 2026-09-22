<link rel="stylesheet" href="{{ asset('css/cms.css') }}?v={{ filemtime(public_path('css/cms.css')) }}">
<link rel="stylesheet" href="{{ asset('css/web-content.css') }}?v={{ filemtime(public_path('css/web-content.css')) }}">
<link rel="stylesheet" href="{{ asset('vendor/jodit/jodit.min.css') }}">
<style>
    .fi-publication-edit-action, .fi-publication-edit-action * { color: #52773a !important; text-decoration: none !important; }
    .fi-publication-edit-action:hover, .fi-publication-edit-action:hover * { color: #365625 !important; text-decoration: none !important; }
</style>
<script src="{{ asset('vendor/jodit/jodit.min.js') }}" defer></script>
