<div>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        @if($quotes->isEmpty())
            <div class="p-6 text-center text-gray-500">
                No favorite quotes yet. Start adding some from the Quote of the Day!
            </div>
        @else
            <div class="divide-y divide-gray-200">
                @foreach($quotes as $quote)
                    <div class="p-6">
                        <div class="text-lg font-medium text-gray-900 mb-2">
                            {{ $quote->body }}
                        </div>
                        <div class="text-sm text-gray-500 mb-4">
                            - {{ $quote->author }}
                        </div>
                        <livewire:quote-card :quote="$quote" :wire:key="'quote-'.$quote->id" />
                    </div>
                @endforeach
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $quotes->links() }}
            </div>
        @endif
    </div>
</div> 