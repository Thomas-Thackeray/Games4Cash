@if ($paginator->hasPages())
@php
$prev = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>';
$next = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>';
@endphp
<nav class="pagination" aria-label="Pagination">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <span class="page-btn" style="opacity:0.35; cursor:default;" aria-disabled="true">{!! $prev !!}</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn" aria-label="Previous">{!! $prev !!}</a>
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
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn" aria-label="Next">{!! $next !!}</a>
    @else
        <span class="page-btn" style="opacity:0.35; cursor:default;" aria-disabled="true">{!! $next !!}</span>
    @endif

</nav>
@endif
