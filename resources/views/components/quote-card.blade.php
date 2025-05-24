@props(['quote', 'showActions' => true])

<div class="{{ $quote['is_long'] ? 'md:col-span-2 lg:col-span-2' : '' }}">
    <div class="bg-white rounded-lg shadow-md p-6 h-full flex flex-col justify-between">
        <div>
            <p class="text-gray-700 text-lg mb-6">{{ $quote['body'] }}</p>
        </div>
        <div class="flex justify-between items-center border-t pt-4 mt-4">
            <span class="text-gray-500 italic">- {{ $quote['author'] }}</span>
            @if($showActions)
                <div class="flex items-center space-x-4">
                    <button
                        wire:click="toggleFavorite('{{ $quote['id'] }}')"
                        class="text-2xl focus:outline-none"
                        title="{{ $quote['is_favorite'] ? 'Remove from favorites' : 'Add to favorites' }}"
                        aria-label="{{ $quote['is_favorite'] ? 'Remove from favorites' : 'Add to favorites' }}"
                    >
                        @if($quote['is_favorite'])
                            <i class="fas fa-heart text-red-500"></i>
                        @else
                            <i class="far fa-heart text-gray-400 hover:text-red-500 transition-colors"></i>
                        @endif
                    </button>
                    <button
                        class="text-gray-400 hover:text-blue-500 transition-colors copy-quote-btn"
                        onclick="navigator.clipboard.writeText(`{{ $quote['body'] }} - {{ $quote['author'] }}`); window.dispatchEvent(new CustomEvent('copied-quote'));"
                        title="Copy to clipboard"
                        type="button"
                        aria-label="Copy quote"
                    >
                        <i class="fas fa-copy"></i>
                    </button>
                    <a
                        href="https://twitter.com/intent/tweet?text={{ urlencode($quote['body'] . ' - ' . $quote['author']) }}"
                        target="_blank"
                        class="text-gray-400 hover:text-blue-400 transition-colors"
                        title="Share on Twitter"
                        aria-label="Share on Twitter"
                    >
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    window.addEventListener('copied-quote', () => {
        let toast = document.createElement('div');
        toast.innerText = 'Copied!';
        toast.className = 'fixed bottom-8 right-8 bg-blue-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade-in-out';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 1500);
    });
    window.addEventListener('favorite-toggled', (e) => {
        let toast = document.createElement('div');
        toast.innerText = e.detail && e.detail.added ? 'Added to favorites!' : 'Removed from favorites!';
        toast.className = 'fixed bottom-8 right-8 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade-in-out';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 1500);
    });
</script>

<style>
@keyframes fade-in-out {
  0% { opacity: 0; transform: translateY(20px); }
  10% { opacity: 1; transform: translateY(0); }
  90% { opacity: 1; transform: translateY(0); }
  100% { opacity: 0; transform: translateY(20px); }
}
.animate-fade-in-out {
  animation: fade-in-out 1.5s;
}
</style>
