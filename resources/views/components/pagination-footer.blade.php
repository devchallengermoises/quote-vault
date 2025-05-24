@props(['paginator'])

@if ($paginator->hasPages())
    <div class="mt-6 flex flex-col items-center">
        {{-- Showing Results Text --}}
        <div class="mb-4 text-center">
            <p class="text-sm text-gray-700 leading-5">
                Showing
                <span class="font-medium">{{ $paginator->firstItem() }}</span>
                to
                <span class="font-medium">{{ $paginator->lastItem() }}</span>
                of
                <span class="font-medium">{{ $paginator->total() }}</span>
                results
            </p>
        </div>

        {{-- Pagination Links --}}
        <div class="text-center">
            {{ $paginator->links() }}
        </div>
    </div>
@endif