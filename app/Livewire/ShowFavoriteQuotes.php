<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ShowFavoriteQuotes extends Component
{
    use WithPagination;

    public function render()
    {
        $user = Auth::user();
        $favorites = $user ? $user->favoriteQuotes()->paginate(6) : collect();
        
        return view('livewire.show-favorite-quotes', [
            'favorites' => $favorites,
        ]);
    }
}
