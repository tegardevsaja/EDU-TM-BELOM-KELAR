@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-center mt-5">
        <span class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-l-md" aria-hidden="true">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 14.707a1 1 0 01-1.414 0L7.586 11l3.707-3.707a1 1 0 011.414 1.414L10.414 11l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            @else
                <button wire:click="previousPage('page')" type="button" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:text-gray-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Prev">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 14.707a1 1 0 01-1.414 0L7.586 11l3.707-3.707a1 1 0 011.414 1.414L10.414 11l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            @endif

            {{-- Page Number Links: kelompok 5 halaman (1-5, 6-10, dst.) --}}
            @php
                $total    = $paginator->lastPage();
                $current  = $paginator->currentPage();
                $perGroup = 5; // jumlah nomor per kelompok

                if ($current <= $perGroup) {
                    // Halaman 1-5: selalu tampil 1..5 (atau sampai lastPage jika <5)
                    $start = 1;
                    $end   = min($perGroup, $total);
                } else {
                    // Halaman di atas 5: kelompok 6-10, 11-15, dst.
                    $groupIndex = intdiv($current - 1, $perGroup); // 1,2,...
                    $start = $groupIndex * $perGroup + 1;
                    $end   = min($start + $perGroup - 1, $total);
                }
            @endphp

            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $current)
                    <span aria-current="page" class="relative inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-blue-600 border border-blue-600 cursor-default">
                        {{ $page }}
                    </span>
                @else
                    <button type="button" wire:click="gotoPage({{ $page }}, 'page')" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {{ $page }}
                    </button>
                @endif
            @endfor

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage('page')" type="button" class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:text-gray-700 focus:z-10 focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Next">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 5.293a1 1 0 011.414 0L12.414 9 8.707 12.707a1 1 0 01-1.414-1.414L10.586 9 7.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            @else
                <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-r-md" aria-hidden="true">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 5.293a1 1 0 011.414 0L12.414 9 8.707 12.707a1 1 0 01-1.414-1.414L10.586 9 7.293 6.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </span>
            @endif
        </span>
    </nav>
@endif
