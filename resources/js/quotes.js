document.addEventListener('DOMContentLoaded', function () {
    function showToast(message) {
        let toast = document.createElement('div');
        toast.textContent = message;
        toast.className = 'fixed bottom-8 left-1/2 transform -translate-x-1/2 bg-blue-600 text-white px-4 py-2 rounded shadow-lg z-50 animate-fade-in';
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.add('opacity-0');
            setTimeout(() => toast.remove(), 500);
        }, 1500);
    }
    function attachCopyListeners() {
        document.querySelectorAll('.copy-quote-btn').forEach(btn => {
            btn.onclick = function () {
                const text = btn.getAttribute('data-quote');
                navigator.clipboard.writeText(text);
                btn.classList.add('text-green-500');
                showToast('Copied!');
                setTimeout(() => btn.classList.remove('text-green-500'), 1000);
            };
        });
    }
    attachCopyListeners();
    // Re-attach after Livewire updates
    if (window.Livewire) {
        window.Livewire.hook('message.processed', attachCopyListeners);
    }
});

// Animación fade-in para el toast
const style = document.createElement('style');
style.innerHTML = `
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.animate-fade-in { animation: fadeIn 0.3s; }
.opacity-0 { opacity: 0 !important; transition: opacity 0.5s; }
`;
document.head.appendChild(style); 