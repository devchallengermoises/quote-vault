@props(['text'])

<button 
    {{ $attributes->merge([
        'class' => 'text-gray-500 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200',
        'title' => 'Copy to clipboard',
        'aria-label' => 'Copy to clipboard',
        'x-data' => '{}',
        'x-on:click' => 'navigator.clipboard.writeText($el.getAttribute("data-text")).then(() => $dispatch("copied-quote"))',
        'data-text' => $text
    ]) }}
>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
    </svg>
</button> 