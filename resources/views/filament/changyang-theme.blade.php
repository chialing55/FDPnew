<link rel="stylesheet" href="{{ asset('css/cms.css') }}?v={{ filemtime(public_path('css/cms.css')) }}">
<link rel="stylesheet" href="{{ asset('css/web-content.css') }}?v={{ filemtime(public_path('css/web-content.css')) }}">
<link rel="stylesheet" href="{{ asset('css/changyang.css') }}?v={{ filemtime(public_path('css/changyang.css')) }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Karla:wght@400;600;700&amp;display=swap" media="print" onload="this.media='all'">
<style>
    :root {
        --cms-forest-900: #4d4741;
        --cms-forest-800: #625b54;
        --cms-forest-700: #75685d;
        --cms-leaf-500: #aa9988;
        --cms-mist: #f7f5f3;
    }
    .fi-sidebar { background: linear-gradient(180deg, #4d4741, #625b54); }
    .fi-sidebar-item-active > a { box-shadow: inset 3px 0 #c4b4a3; }
    .fi-header-heading { color: #4d4741; }
    .fi-topbar nav { border-bottom-color: #e3ded9; box-shadow: 0 1px 2px rgba(77, 71, 65, .06); }
    .fi-section, .fi-ta-ctn { border-color: #e3ded9; box-shadow: 0 1px 3px rgba(77, 71, 65, .07); }
    .web-content h2, .web-content a { color: #625b54; }
    .web-content h3 { color: #75685d; }
    .fi-changyang-list-table-wrap { overflow-x:auto; border:1px solid #e7e2dc; border-radius:.75rem; background:#fff; }
    .fi-changyang-list-table { width:100%; min-width:50rem; border-collapse:collapse; text-align:left; }
    .fi-changyang-list-table th { padding:1rem; border-bottom:1px solid #e7e2dc; color:#231f20; font-size:.9rem; font-weight:700; }
    .fi-changyang-list-table td { padding:1rem; border-top:1px solid #e7e2dc; vertical-align:middle; }
    .fi-changyang-list-table__row { cursor:pointer; transition:background-color .15s; }
    .fi-changyang-list-table__row:hover { background:#faf8f6; }
    .fi-changyang-list-table__nowrap { white-space:nowrap; }
    .fi-changyang-list-table__actions { width:7.5rem; text-align:right; white-space:nowrap; }
    .fi-changyang-list-table__edit { display:inline-flex; align-items:center; gap:.35rem; color:#52773a; font-weight:700; text-decoration:none; }
    .fi-changyang-list-table__edit:hover { color:#365625; text-decoration:none; }
    .fi-changyang-list-table__published, .fi-changyang-list-table__hidden { width:1.6rem; height:1.6rem; }
    .fi-changyang-list-table__published { color:#20b965; }
    .fi-changyang-list-table__hidden { color:#a9a29a; }
    .fi-changyang-dashboard { max-width: 72rem; }
    .fi-changyang-dashboard__heading { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; }
    .fi-changyang-dashboard__heading h2 { margin:0; color:#4d4741; font-size:1.25rem; font-weight:700; }
    .fi-changyang-dashboard__heading p { margin:.25rem 0 0; color:#75685d; font-size:.875rem; }
    .fi-changyang-dashboard__site-link { display:inline-flex; align-items:center; gap:.375rem; color:#625b54; font-size:.875rem; font-weight:600; white-space:nowrap; }
    .fi-changyang-dashboard__grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; }
    .fi-changyang-dashboard__card { display:flex; align-items:center; gap:.875rem; min-height:104px; padding:1rem; border:1px solid #e3ded9; border-radius:.75rem; background:#fff; box-shadow:0 1px 3px rgba(77,71,65,.07); transition:border-color .15s, box-shadow .15s, transform .15s; }
    .fi-changyang-dashboard__card:hover { border-color:#aa9988; box-shadow:0 5px 14px rgba(77,71,65,.12); transform:translateY(-2px); }
    .fi-changyang-dashboard__icon { display:grid; flex:none; place-items:center; width:3rem; height:3rem; border-radius:.625rem; color:#625b54; background:#f0ece8; }
    .fi-changyang-dashboard__content { display:flex; min-width:0; flex:1; flex-direction:column; gap:.15rem; }
    .fi-changyang-dashboard__label { overflow:hidden; color:#4d4741; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
    .fi-changyang-dashboard__title { overflow:hidden; color:#8a8179; font-size:.8125rem; text-overflow:ellipsis; white-space:nowrap; }
    .fi-changyang-dashboard__draft { border-radius:9999px; background:#f5ede0; color:#946b2d; padding:.15rem .45rem; font-size:.6875rem; font-weight:600; white-space:nowrap; }
    .fi-changyang-dashboard__arrow { color:#9a8e84; font-size:1.25rem; }
    .fi-changyang-hero-preview { overflow:hidden; border:1px solid #e3ded9; border-radius:.75rem; background:#faf8f6; }
    .fi-changyang-hero-preview__label { display:block; padding:.65rem .9rem; border-bottom:1px solid #e3ded9; color:#625b54; font-size:.875rem; font-weight:700; }
    .fi-changyang-hero-preview__image { display:block; width:100%; max-height:24rem; object-fit:contain; background:#f0ece8; }
    @media (max-width: 640px) { .fi-changyang-dashboard__heading { align-items:flex-start; flex-direction:column; } }
</style>
