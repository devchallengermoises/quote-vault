<?php

namespace App\Livewire;

use App\Services\QuoteService;
use Livewire\Component;
use Illuminate\Support\Collection;

class QuoteGrid extends Component
{
    public string $mode = 'random';
    public Collection $quotes;

    protected $listeners = ['quoteFavorited' => '$refresh'];

    public function mount(string $mode = 'random')
    {
        $this->mode = $mode;
        $this->loadQuotes();
    }

    public function loadQuotes()
    {
        $quoteService = app(QuoteService::class);
        
        if ($this->mode === 'favorites') {
            $this->quotes = $quoteService->getFavoriteQuotes();
        } else {
            $this->quotes = $quoteService->getRandomQuotes(50);
        }
    }

    public function toggleFavorite(string $quoteId)
    {
        try {
            $quoteService = app(QuoteService::class);
            $added = $quoteService->toggleFavorite($quoteId);
            
            // Solo actualizamos la lista si estamos en modo favoritos
            if ($this->mode === 'favorites') {
                $this->loadQuotes();
            }
            
            // Notificamos a otros componentes
            $this->dispatch('quoteFavorited');
            $this->dispatch('favorite-toggled', quoteId: $quoteId, added: $added);
        } catch (\Exception $e) {
            \Log::error('Error toggling favorite', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.quote-grid', [
            'quotes' => $this->quotes,
            'mode' => $this->mode
        ]);
    }
} 