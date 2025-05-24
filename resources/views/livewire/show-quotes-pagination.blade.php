<div>
    @if($paginator && $paginator->hasPages())
        <div class="w-full flex justify-center mt-6">
            <div class="flex flex-col items-center">
                {{-- Info Text --}}
                <div class="text-sm text-gray-700 mb-4">
                    Showing
                    <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    results
                </div>

                {{-- Pagination Links --}}
                <div class="flex items-center space-x-2">
                    {{-- Previous Page Link --}}
                    @if($paginator->onFirstPage())
                        <span class="px-3 py-1 text-gray-400 cursor-not-allowed">
                            Previous
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">
                            Previous
                        </a>
                    @endif

                    {{-- Page Numbers --}}
                    @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                        @if($page == $paginator->currentPage())
                            <span class="px-3 py-1 bg-blue-600 text-white rounded">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1 text-blue-600 hover:text-blue-800">
                            Next
                        </a>
                    @else
                        <span class="px-3 py-1 text-gray-400 cursor-not-allowed">
                            Next
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div> 