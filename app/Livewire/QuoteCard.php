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
        $this->isFavorite = Cache::remember(
            'quote_favorite_' . auth()->id() . '_' . $quote->id,
            300,
            fn() => auth()->user()->favoriteQuotes()->where('quotes.id', $quote->id)->exists()
        );
    }

    public function toggleFavorite()
    {
        $this->showAnimation = true;

        if ($this->isFavorite) {
            auth()->user()->favoriteQuotes()->detach($this->quote->id);
            $this->isFavorite = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Quote removed from favorites!'
            ]);
        } else {
            auth()->user()->favoriteQuotes()->attach($this->quote->id);
            $this->isFavorite = true;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Quote added to favorites!'
            ]);
        }

        // Clear relevant caches
        Cache::forget('quote_favorite_' . auth()->id() . '_' . $this->quote->id);
        Cache::forget('user_favorites_' . auth()->id());

        $this->dispatch('resetAnimation');
    }

    public function render()
    {
        return view('livewire.quote-card');
    }
} 