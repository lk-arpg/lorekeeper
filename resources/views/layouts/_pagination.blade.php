@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">&lsaquo;</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item pageSelectPopover" data-container="body" data-toggle="popover" data-placement="top" data-title="Jump to Page" data-html="true" data-content="<div class='paginationPopoverContent'></div>">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>

    @once
        <div class="pagination-popover-origin d-flex align-items-center hide" style="gap: 10px;">
            <input type="range" class="form-control-range custom-range paginationPageRange" min="1" max="{{ $paginator->lastPage() }}" value="{{ $paginator->currentPage() }}" oninput="this.nextElementSibling.value = this.value">
            <input type="number" style="flex: 1 0 35px; height: 24px;" class="paginationPageText form-control form-control-sm py-0 px-1" min="1" max="{{ $paginator->lastPage() }}" value="{{ $paginator->currentPage() }}"
                oninput="this.previousElementSibling.value = this.value">
            <span class="badge badge-primary paginator-btn p-1 px-2" style="cursor: pointer; font-size: 14px">Go</span>
        </div>
    @endonce
@endif
