<?php

namespace App\Livewire;

use App\Services\QuoteService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class Favorites extends Component
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

    public function loadQuotes()
    {
        $quoteService = app(QuoteService::class);
        $allQuotes = $quoteService->getFavoriteQuotes();
        $offset = ($this->page - 1) * $this->perPage;
        $this->quotes = $allQuotes->slice($offset, $this->perPage);
    }

    public function toggleFavorite($quoteId)
    {
        try {
            $quoteService = app(QuoteService::class);
            $added = $quoteService->toggleFavorite($quoteId);
            $this->loadQuotes();
            $this->dispatch('quoteFavorited');
            $this->dispatch('favorite-toggled', added: $added);
        } catch (\Exception $e) {
            \Log::error('Error toggling favorite', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        $quoteService = app(QuoteService::class);
        $allQuotes = $quoteService->getFavoriteQuotes();
        $totalCount = $allQuotes->count();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $this->quotes,
            $totalCount,
            $this->perPage,
            $this->page,
            ['path' => request()->url()]
        );
        return view('livewire.favorites', [
            'quotes' => $this->quotes,
            'paginator' => $paginator
        ]);
    }
} 