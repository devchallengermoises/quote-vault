document.addEventListener('DOMContentLoaded', function() {
    // Handle favorite toggling
    document.querySelectorAll('[id^="favorite-form-"]').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(form);
            const actionUrl = form.getAttribute('action');

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const favoriteIcon = form.querySelector('.favorite-icon');
                if (favoriteIcon) {
                    if (data.isFavorite) {
                        favoriteIcon.classList.remove('text-gray-400', 'stroke-current');
                        favoriteIcon.classList.add('text-red-500', 'fill-current');
                        favoriteIcon.innerHTML = '<path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />';
                    } else {
                        favoriteIcon.classList.remove('text-red-500', 'fill-current');
                        favoriteIcon.classList.add('text-gray-400', 'stroke-current');
                        favoriteIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />';
                    }
                    form.dataset.isFavorite = data.isFavorite;
                }
            })
            .catch(error => {
                console.error('Error toggling favorite:', error);
                alert('Failed to toggle favorite.');
            });
        });
    });

    // Handle copy button
    document.querySelectorAll('[data-copy-text]').forEach(button => {
        button.addEventListener('click', function() {
            const text = this.dataset.copyText;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.textContent.trim();
                this.textContent = 'Copied!';
                setTimeout(() => {
                    this.innerHTML = `<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>${originalText}`;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Failed to copy text to clipboard');
            });
        });
    });
}); 