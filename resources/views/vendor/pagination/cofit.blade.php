<nav class="cofit-pagination">
    {{-- info kiri --}}
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
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev">
                    &laquo;
                </a>
            </li>
        @endif

        {{-- Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="disabled">
                    <span>{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active">
                            <span>{{ $page }}</span>
                        </li>
                    @else
                        <li>
                            <a href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li>
                <a href="{{ $paginator->nextPageUrl() }}" rel="next">
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
