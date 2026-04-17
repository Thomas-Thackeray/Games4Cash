@if ($paginator->hasPages())
<nav class="pagination" aria-label="Pagination">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="page-btn" style="opacity:0.35; cursor:default;" aria-disabled="true">&#8249;</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn" aria-label="Previous">&lsaquo;</a>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="page-btn" style="opacity:0.5; cursor:default;">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-btn active" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn" aria-label="Next">&rsaquo;</a>
    @else
        <span class="page-btn" style="opacity:0.35; cursor:default;" aria-disabled="true">&#8250;</span>
    @endif

</nav>
@endif
