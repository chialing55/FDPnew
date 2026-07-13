<div class="space-y-4">
    <ul class="divide-y divide-gray-200 bg-white">
        @forelse ($publications as $publication)
            <li class="px-4 py-4">
                <div class="font-medium text-gray-900">{!! $publication->citation_html ?: e($publication->title) !!}</div>
                <div class="mt-2 flex flex-wrap gap-4 text-sm">
                    @if ($publication->doi)
                        <a href="{{ 'https://doi.org/' . preg_replace('#^https?://(?:dx\.)?doi\.org/#i', '', $publication->doi) }}" target="_blank" rel="noopener" class="text-forest">DOI</a>
                    @endif
                    @if ($publication->url)
                        <a href="{{ $publication->url }}" target="_blank" rel="noopener" class="text-forest">文章頁面</a>
                    @endif
                    @if ($publication->pdf_path)
                        <a href="{{ Storage::disk('public')->url($publication->pdf_path) }}" target="_blank" rel="noopener" class="text-forest">下載 PDF</a>
                    @endif
                </div>
            </li>
        @empty
            <li class="px-4 py-6 text-gray-600">目前尚無學術產出資料。</li>
        @endforelse
    </ul>
    {{ $publications->links() }}
</div>
