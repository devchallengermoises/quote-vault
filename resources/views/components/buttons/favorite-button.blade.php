@props(['quoteId', 'isFavorite'])

<button
    {{ $attributes->merge([
        'class' => 'text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-500 transition-colors duration-200',
        'title' => $isFavorite ? 'Remove from favorites' : 'Add to favorites',
        'aria-label' => $isFavorite ? 'Remove from favorites' : 'Add to favorites',
        'x-data' => '{}',
        'x-on:click' => '$wire.toggleFavorite($el.getAttribute("data-quote-id"))',
        'data-quote-id' => $quoteId
    ]) }}
>
    <svg class="w-6 h-6 {{ $isFavorite ? 'text-red-500' : 'text-gray-500' }}" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
</button> 