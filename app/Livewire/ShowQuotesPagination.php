<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Quote;
use Livewire\WithPagination;

class ShowQuotesPagination extends Component
{
    use WithPagination;

    public function render()
    {
        $quotes = Quote::paginate(12);
        
        return view('livewire.show-quotes-pagination', [
            'paginator' => $quotes,
        ]);
    }
} 