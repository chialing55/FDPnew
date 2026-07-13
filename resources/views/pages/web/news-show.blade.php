@extends('layouts/webapp')
@section('title', $news->title . ' - ' . __('web.title'))
@section('content')
    <article class="mx-auto max-w-5xl rounded-lg bg-white p-6 md:p-10">
        <div class="mb-3 text-sm text-gray-500">{{ $news->publish_date }}</div>
        <h1 class="mb-6 text-3xl font-bold text-forest-dark">{{ $news->title }}</h1>
        @if ($news->cover_image)
            <img src="{{ Storage::disk('public')->url($news->cover_image) }}" alt="" class="mb-6 max-h-[32rem] w-full rounded-lg object-cover">
        @endif
        <div class="web-content prose max-w-none">{!! $news->content !!}</div>
    </article>
@endsection
