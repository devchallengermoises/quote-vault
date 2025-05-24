<?php

namespace App\Livewire;

use App\Models\Quote;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class QuoteCard extends Component
{
    public Quote $quote;
    public bool $isFavorite = false;
    public bool $showAnimation = false;

    public function mount(Quote $quote)
    {
        $this->quote = $quote;
        $this->isFavorite = $quote->isFavoritedBy(auth()->user());
    }

    public function toggleFavorite()
    {
        $this->showAnimation = true;
        $this->isFavorite = $this->quote->toggleFavorite(auth()->user());
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $this->isFavorite ? 'Quote added to favorites!' : 'Quote removed from favorites!'
        ]);

        $this->dispatch('resetAnimation');
    }

    public function render()
    {
        return view('livewire.quote-card');
    }
} 