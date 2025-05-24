<div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($quotes as $quote)
            <x-quote-card :quote="array_merge($quote, ['is_favorite' => true])" :showActions="true" />
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500">No favorite quotes yet.</p>
            </div>
        @endforelse
    </div>

    <x-pagination :paginator="$paginator" />
</div> 