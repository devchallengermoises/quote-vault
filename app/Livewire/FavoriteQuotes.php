<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class FavoriteQuotes extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.favorite-quotes', [
            'quotes' => auth()->user()->favoriteQuotes()->paginate(10)
        ]);
    }
} 