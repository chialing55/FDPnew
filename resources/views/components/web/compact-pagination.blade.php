@if ($paginator->hasPages())
    <nav class="flex justify-end" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <span class="relative inline-flex rounded-md shadow-sm">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true"
                    class="inline-flex cursor-default items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-gray-400">
                    <span aria-hidden="true">‹</span>
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    class="inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-gray-600 hover:bg-gray-50"
                    aria-label="{{ __('pagination.previous') }}">‹</button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="-ml-px inline-flex items-center border border-gray-300 bg-white px-3 py-2 text-sm text-gray-500">
                        {{ $element }}
                    </span>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page === $paginator->currentPage())
                            <span aria-current="page"
                                class="-ml-px inline-flex items-center border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-forest">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                class="-ml-px inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    class="-ml-px inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-gray-600 hover:bg-gray-50"
                    aria-label="{{ __('pagination.next') }}">›</button>
            @else
                <span aria-disabled="true"
                    class="-ml-px inline-flex cursor-default items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-gray-400">
                    <span aria-hidden="true">›</span>
                </span>
            @endif
        </span>
    </nav>
@endif
