<?php

namespace App\Livewire;

use App\Services\QuoteService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ShowQuotes extends Component
{
    use WithPagination;

    protected $listeners = ['quoteFavorited' => '$refresh', 'toggleFavorite'];
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

    public function toggleFavorite($quoteId)
    {
        try {
            $quoteService = app(QuoteService::class);
            $added = $quoteService->toggleFavorite($quoteId);
            $this->loadQuotes();
            $this->dispatch('quoteFavorited');
            $this->dispatch('favorite-toggled', quoteId: $quoteId, added: $added);
        } catch (\Exception $e) {
            \Log::error('Error toggling favorite', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function loadQuotes()
    {
        try {
            $quoteService = app(QuoteService::class);
            
            // Use cache key based on page and perPage
            $cacheKey = "quotes_page_{$this->page}_{$this->perPage}";
            
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
        $totalCount = Cache::remember('total_quotes_count', 300, function () use ($quoteService) {
            return $quoteService->getRandomQuotes(50)->count();
        });
        
        $paginator = new LengthAwarePaginator(
            $this->quotes,
            $totalCount,
            $this->perPage,
            $this->page,
            ['path' => request()->url()]
        );

        return view('livewire.show-quotes', [
            'quotes' => $this->quotes,
            'paginator' => $paginator
        ]);
    }
}
