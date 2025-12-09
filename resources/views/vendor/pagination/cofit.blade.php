@if ($paginator->hasPages())
@php
    $window  = 3;
    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();

    $start = max(1, $current - 1);
    $end   = min($last, $start + ($window - 1));

    if (($end - $start + 1) < $window) {
        $start = max(1, $end - ($window - 1));
    }

    $baseQuery = request()->except('page');

    // anchor ke bagian tabel produk
    $anchor = '#produk-table';
@endphp

<nav class="cofit-pagination">
    <div class="cofit-pagination__info">
        Showing
        @if ($paginator->firstItem())
            {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }}
            of {{ $paginator->total() }} results
        @else
            {{ $paginator->count() }}
        @endif
    </div>

    <ul class="cofit-pagination__list">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="disabled">
                <span>&laquo;</span>
            </li>
        @else
            <li>
                <a href="{{ $paginator->appends($baseQuery)->previousPageUrl() . $anchor }}" rel="prev">
                    &laquo;
                </a>
            </li>
        @endif

        {{-- ANGKA HALAMAN (window 3) --}}
        @for ($page = $start; $page <= $end; $page++)
            @php
                $url = $paginator->appends($baseQuery)->url($page) . $anchor;
            @endphp

            @if ($page == $paginator->currentPage())
                <li class="active">
                    <span>{{ $page }}</span>
                </li>
            @else
                <li>
                    <a href="{{ $url }}">{{ $page }}</a>
                </li>
            @endif
        @endfor

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li>
                <a href="{{ $paginator->appends($baseQuery)->nextPageUrl() . $anchor }}" rel="next">
                    &raquo;
                </a>
            </li>
        @else
            <li class="disabled">
                <span>&raquo;</span>
            </li>
        @endif
    </ul>
</nav>
@endif
