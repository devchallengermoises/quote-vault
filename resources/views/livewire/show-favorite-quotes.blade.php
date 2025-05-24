@if(count($favorites) > 0)
    @foreach($favorites as $favorite)
        <x-quote-card :quote="$favorite" :showActions="true" wire:key="favorite-{{ $favorite['id'] ?? $favorite->id }}" />
    @endforeach
@else
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <p class="text-gray-500">No favorite quotes available at the moment.</p>
    </div>
@endif
