@props(['paginator'])

@if($paginator->hasPages())
    <div class="mt-8 flex flex-col items-center">
        <div class="flex items-center space-x-2">
            <button
                wire:click="previousPage"
                @if($paginator->onFirstPage()) disabled @endif
                class="p-2 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:text-gray-300 disabled:cursor-not-allowed"
            >&lt;</button>
            @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                <button
                    wire:click="gotoPage({{ $i }})"
                    @if($paginator->currentPage() == $i) disabled @endif
                    class="px-3 py-1 rounded-lg border border-gray-200 mx-1
                        {{ $paginator->currentPage() == $i ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-blue-50' }}
                        disabled:cursor-not-allowed"
                >{{ $i }}</button>
            @endfor
            <button
                wire:click="nextPage"
                @if(!$paginator->hasMorePages()) disabled @endif
                class="p-2 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 disabled:text-gray-300 disabled:cursor-not-allowed"
            >&gt;</button>
        </div>
        <div class="text-xs text-gray-400 mt-2">
            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} results
        </div>
    </div>
@endif 