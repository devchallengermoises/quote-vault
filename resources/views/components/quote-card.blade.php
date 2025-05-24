@props(['quote', 'isFavorite' => false])

@php
    $body = $quote['body'] ?? $quote->body; // Handle both array and object
    $author = $quote['author'] ?? $quote->author; // Handle both array and object
    $externalId = $quote['id'] ?? $quote->external_id; // Handle both array and object
@endphp

{{-- Quote Card Container --}}
<div class="bg-white overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300 sm:rounded-lg p-6 flex flex-col h-[380px] w-full">
    {{-- Content Area (Text and Author) --}}
    <div class="flex-grow flex flex-col">
        {{-- Text Container --}}
        <div class="flex-grow overflow-y-auto pr-4">
            <p class="text-lg font-medium text-gray-900 break-words text-justify leading-relaxed">{{ $body }}</p>
        </div>
        {{-- Author --}}
        <div class="flex-none h-6 text-sm text-gray-500 mt-2">
            - {{ $author }}
        </div>
    </div>

    {{-- Footer Area (Buttons) --}}
    <div class="flex-none h-10 flex items-center mt-4">
        {{-- Action Buttons --}}
        <div class="flex items-center space-x-2">
            {{-- Favorite Button --}}
            <form id="favorite-form-{{ $externalId }}" action="{{ route('quotes.toggle-favorite', ['quote' => $externalId]) }}" 
                  method="POST" 
                  class="inline-flex items-center justify-center p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
                @csrf
                <button 
                    type="submit"
                    class="h-6 w-6 bg-transparent border-none p-0 favorite-button"
                    data-quote-id="{{ $externalId }}"
                >
                    @if($isFavorite)
                        <svg class="w-6 h-6 text-red-500 fill-current favorite-icon" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-gray-400 stroke-current favorite-icon" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    @endif
                </button>
            </form>
            {{-- Copy Button (will likely not work without JS) --}}
            <button 
                class="inline-flex items-center p-2 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm text-gray-700"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
                Copy
            </button>
            {{-- Tweet Button --}}
            <a 
                href="https://twitter.com/intent/tweet?text={{ urlencode($body . ' - ' . $author) }}"
                target="_blank"
                class="inline-flex items-center p-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus::ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 text-sm"
            >
                 <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                </svg>
                Tweet
            </a>
        </div>

        {{-- Text Pagination Controls (removed) --}}
        <div class="flex items-center space-x-2 min-w-[120px] justify-end">
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('favorite-form-{{ $externalId }}');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission (page refresh)

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json' // Indicate that we expect a JSON response
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Update the heart icon based on the response
                    const favoriteIcon = form.querySelector('.favorite-icon');
                    if (favoriteIcon) {
                        if (data.isFavorite) {
                            // Change to filled red heart icon
                            favoriteIcon.classList.remove('text-gray-400', 'stroke-current');
                            favoriteIcon.classList.add('text-red-500', 'fill-current');
                            favoriteIcon.innerHTML = '<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />';
                        } else {
                            // Change to outlined gray heart icon
                            favoriteIcon.classList.remove('text-red-500', 'fill-current');
                            favoriteIcon.classList.add('text-gray-400', 'stroke-current');
                            favoriteIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />';
                        }
                         // Update the isFavorite status on the button's data attribute if needed for other JS
                         form.querySelector('.favorite-button').dataset.isFavorite = data.isFavorite;
                    }

                    // You could dispatch a custom event here if other parts of the page need to react
                    // document.dispatchEvent(new CustomEvent('quote-favorited', { detail: { quoteId: '{{ $externalId }}', isFavorite: data.isFavorite } }));

                })
                .catch(error => {
                    console.error('Error toggling favorite:', error);
                    // Log the full error object to the console for debugging
                    console.error('Full error details:', error);
                    // Re-show the simple alert for user feedback
                    alert('Failed to toggle favorite.');
                });
            });
        }
    });
</script>
