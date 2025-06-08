@if(count($quotes) > 0)
    @foreach($quotes as $quoteData)
        @php
            $isFavorite = auth()->user()->favoriteQuotes()
                ->where('external_id', $quoteData['quote']['id'])
                ->exists();
        @endphp
        <x-quote-card :quote="$quoteData['quote']" :isFavorite="$isFavorite" wire:key="quote-{{ $quoteData['quote']['id'] }}" />
    @endforeach
@else
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <p class="text-gray-500">No quotes available at the moment.</p>
    </div>
@endif
