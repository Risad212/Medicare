@if ($paginator->hasPages())
    <div class="pagination-wrap">
        <ul class="pagination-list">
            @unless ($paginator->onFirstPage())
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page">&#8249;</a>
                </li>
            @endunless

            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                <li class="{{ $page == $paginator->currentPage() ? 'active' : '' }}">
                    <a href="{{ $url }}">{{ $page }}</a>
                </li>
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page">&#8250;</a>
                </li>
            @endif
        </ul>
    </div>
@endif
