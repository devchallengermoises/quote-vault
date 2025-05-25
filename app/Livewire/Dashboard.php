<?php

namespace App\Livewire;

use App\Services\QuoteService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Component
{
    use WithPagination;

    protected $listeners = [
        'quoteFavorited' => '$refresh',
        'favorite-toggled' => 'handleFavoriteToggled'
    ];
    
    protected $paginationTheme = 'tailwind';
    public $quotes = [];
    public $perPage = 12;
    public $page = 1;

    public function mount()
    {
        $this->page = request()->get('page', 1);
        $this->perPage = 12;
        $this->loadQuotes();
    }

    public function updatingPage($page)
    {
        $this->page = $page;
        $this->loadQuotes();
    }

    public function handleFavoriteToggled($quoteId, $added)
    {
        // Update the favorite status in the current view
        $this->quotes = collect($this->quotes)->map(function ($quote) use ($quoteId, $added) {
            if ($quote['id'] === $quoteId) {
                $quote['is_favorite'] = $added;
            }
            return $quote;
        })->toArray();
    }

    public function toggleFavorite($quoteId)
    {
        try {
            $quoteService = app(QuoteService::class);
            $added = $quoteService->toggleFavorite($quoteId);
            
            // Clear all caches
            $quoteService->clearAllCaches($quoteId);
            
            // Reload quotes
            $this->loadQuotes();
            
            // Dispatch events
            $this->dispatch('quoteFavorited');
            $this->dispatch('favorite-toggled', quoteId: $quoteId, added: $added);
            
            // Show toast notification
            $this->dispatch('show-toast', [
                'message' => $added ? 'Quote added to favorites' : 'Quote removed from favorites',
                'type' => 'success'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling favorite', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('show-toast', [
                'message' => 'Error updating favorite status',
                'type' => 'error'
            ]);
        }
    }

    public function loadQuotes()
    {
        try {
            $quoteService = app(QuoteService::class);
            $userId = auth()->id();
            
            // Use cache key based on user, page and perPage
            $cacheKey = "quotes_all_user_{$userId}_page_{$this->page}_{$this->perPage}";
            
            // Try to get from cache first
            $cachedQuotes = Cache::get($cacheKey);
            if ($cachedQuotes) {
                $this->quotes = collect($cachedQuotes);
                return;
            }
            
            // If not in cache, get from service
            $allQuotes = $quoteService->getRandomQuotes(50);
            
            // Calculate offset for current page
            $offset = ($this->page - 1) * $this->perPage;
            
            // Get only quotes for current page
            $this->quotes = $allQuotes->slice($offset, $this->perPage);
            
            // Cache the results for 5 minutes
            Cache::put($cacheKey, $this->quotes->toArray(), 300);
            
            \Log::info('Quotes loaded successfully', [
                'total_quotes' => $allQuotes->count(),
                'quotes_on_page' => $this->quotes->count(),
                'current_page' => $this->page,
                'per_page' => $this->perPage
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading quotes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->quotes = collect([]);
        }
    }

    public function render()
    {
        $quoteService = app(QuoteService::class);
        
        // Get total count from Redis or cache
        $userId = auth()->id();
        $totalCount = Cache::remember("quotes_count_all_user_{$userId}", 300, function () use ($quoteService) {
            return $quoteService->getRandomQuotes(50)->count();
        });
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $this->quotes,
            $totalCount,
            $this->perPage,
            $this->page,
            ['path' => request()->url()]
        );

        return view('livewire.dashboard', [
            'quotes' => $this->quotes,
            'paginator' => $paginator
        ]);
    }
} 