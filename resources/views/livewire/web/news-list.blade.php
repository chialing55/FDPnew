<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        @forelse ($newsItems as $news)
            <a href="{{ $news->external_url ?: route('news.show', $news) }}"
                @if ($news->external_url) target="_blank" rel="noopener" @endif
                class="group flex overflow-hidden rounded-lg border bg-white text-gray-900 no-underline transition hover:shadow-md hover:no-underline">
                @if ($news->cover_image)
                    <img src="{{ Storage::disk('public')->url($news->cover_image) }}" alt="" class="w-36 shrink-0 object-cover">
                @endif
                <div class="min-w-0 p-4">
                    <div class="text-xs text-gray-500">{{ $news->publish_date }}</div>
                    <h2 class="mt-1 font-semibold group-hover:text-forest">{{ $news->title }}</h2>
                </div>
            </a>
        @empty
            <p class="col-span-full py-6 text-gray-500">目前尚無最新消息。</p>
        @endforelse
    </div>
    {{ $newsItems->links() }}
</div>
