<div>
    @if(count($quotes) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($quotes as $quote)
                <div 
                    x-data="{ 
                        isRemoving: false,
                        isFavoriting: false,
                        currentPage: 1,
                        totalPages: Math.ceil('{{ strlen($quote['body']) }}' / 200),
                        nextPage() {
                            if (this.currentPage < this.totalPages) {
                                this.currentPage++;
                            }
                        },
                        prevPage() {
                            if (this.currentPage > 1) {
                                this.currentPage--;
                            }
                        },
                        async removeCard() {
                            this.isFavoriting = true;
                            await new Promise(resolve => setTimeout(resolve, 300));
                            this.isRemoving = true;
                            setTimeout(() => {
                                this.$el.remove();
                            }, 300);
                        }
                    }"
                    x-show="!isRemoving"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden flex flex-col h-[320px]"
                >
                    <div class="p-4 flex-1 flex flex-col">
                        <blockquote class="flex-1">
                            <p class="text-base font-serif text-gray-800 dark:text-gray-200 leading-relaxed">
                                "{{ $quote['body'] }}"
                            </p>
                        </blockquote>
                        
                        <div class="mt-3 flex items-center justify-between">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <i class="font-light">— {{ $quote['author'] }}</i>
                            </p>
                            
                            <div x-show="totalPages > 1" class="flex items-center space-x-2">
                                <button 
                                    x-on:click="prevPage()"
                                    x-show="currentPage > 1"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="`${currentPage}/${totalPages}`"></span>
                                <button 
                                    x-on:click="nextPage()"
                                    x-show="currentPage < totalPages"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex justify-end space-x-2">
                            <x-buttons.favorite-button 
                                :quoteId="$quote['id']"
                                :isFavorite="$quote['is_favorite']"
                                x-bind:isFavorite="('{{ $mode }}' === 'favorites' ? $quote['is_favorite'] : !$quote['is_favorite']) || isFavoriting"
                                x-on:click="
                                    isFavoriting = true;
                                    $wire.toggleFavorite($el.getAttribute('data-quote-id')).then(response => {
                                        if ('{{ $mode }}' === 'favorites') {
                                            $dispatch('favorite-removed', { message: 'Removed from favorites!' });
                                            removeCard();
                                        } else {
                                            $dispatch('favorite-added', { message: 'Added to favorites!' });
                                            removeCard();
                                        }
                                        isFavoriting = false;
                                    });
                                "
                            />
                            
                            <x-buttons.copy-button 
                                :text="$quote['body']"
                            />
                            
                            <x-buttons.tweet-button 
                                :text="$quote['body']"
                                :author="$quote['author']"
                            />
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">
                        @if($mode === 'favorites')
                            You haven't added any quotes to your favorites yet.
                        @else
                            No quotes available at the moment.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 text-lg">
                @if($mode === 'favorites')
                    No favorite quotes yet. Start adding some!
                @else
                    No quotes available at the moment.
                @endif
            </p>
        </div>
    @endif
</div>

<!-- Toast Stack Container -->
<div
    x-data="{ 
        toasts: [],
        addToast(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => this.removeToast(id), 2000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(toast => toast.id !== id);
        }
    }"
    x-on:copied-quote.window="addToast('Quote copied to clipboard!')"
    x-on:favorite-added.window="addToast($event.detail.message)"
    x-on:favorite-removed.window="addToast($event.detail.message)"
    class="fixed bottom-4 right-4 z-50 flex flex-col space-y-2"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            :class="{
                'bg-green-500': toast.type === 'success',
                'bg-red-500': toast.type === 'error'
            }"
            class="px-4 py-2 rounded-lg text-white shadow-lg"
            x-text="toast.message"
        ></div>
    </template>
</div>

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