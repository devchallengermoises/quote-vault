<div>
    <livewire:quote-grid mode="favorites" />
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
        const quoteId = e.detail.quoteId;
        const added = e.detail.added;
        const buttons = document.querySelectorAll(`.favorite-btn[data-quote-id="${quoteId}"]`);
        
        buttons.forEach(button => {
            const svg = button.querySelector('svg');
            if (added) {
                svg.classList.remove('text-gray-400', 'stroke-current');
                svg.classList.add('text-red-500', 'fill-current');
                svg.innerHTML = '<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />';
                button.title = 'Remove from favorites';
                button.setAttribute('aria-label', 'Remove from favorites');
            } else {
                svg.classList.remove('text-red-500', 'fill-current');
                svg.classList.add('text-gray-400', 'stroke-current');
                svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />';
                button.title = 'Add to favorites';
                button.setAttribute('aria-label', 'Add to favorites');
            }
        });

        let toast = document.createElement('div');
        toast.innerText = added ? 'Added to favorites!' : 'Removed from favorites!';
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