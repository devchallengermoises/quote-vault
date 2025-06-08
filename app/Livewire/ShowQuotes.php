<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Quote;
use Livewire\WithPagination;
use App\Services\QuoteService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ShowQuotes extends Component
{
    use WithPagination;

    protected $quoteService;

    public function boot(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    public function render()
    {
        Log::info('Livewire ShowQuotes: Starting to fetch quotes from API');

        try {
            // Fetch quotes from the API using the injected service
            $quotesData = $this->quoteService->getRandomQuotes(12);
            Log::info('Livewire ShowQuotes: Quotes received from API data:', ['data' => $quotesData]);

            if ($quotesData->isEmpty()) {
                 Log::warning('Livewire ShowQuotes: No quotes received from API');
                 // Optionally return a view with a message indicating no quotes
                 // return view('livewire.show-quotes', ['quotes' => collect(), 'paginator' => null]);
                 // For now, we'll proceed with an empty collection, the view should handle it
            }

            // Transform the quotes to the format expected by the view
            // The getRandomQuotes now returns a collection of transformed quotes, 
            // so we just need to ensure the structure is correct if needed.
            // Assuming getRandomQuotes returns a Collection where each item 
            // has the structure ['quote' => ['id' => ..., 'body' => ..., 'author' => ...]]
            $transformedQuotes = $quotesData->map(function ($item) {
                 // Adjust this mapping based on the actual structure returned by getRandomQuotes
                 // If getRandomQuotes already returns the desired structure, this map might be simplified or removed
                 return [
                     'quote' => [
                         'id' => $item['quote']['id'] ?? null,
                         'body' => $item['quote']['body'] ?? 'Quote body missing',
                         'author' => $item['quote']['author'] ?? 'Unknown Author',
                     ]
                 ];
            });

            Log::info('Livewire ShowQuotes: Transformed quotes for view:', ['transformed' => $transformedQuotes]);

            // Create a manual paginator for the API results
            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 12;
            $paginator = new LengthAwarePaginator(
                $transformedQuotes->forPage($page, $perPage),
                $transformedQuotes->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            Log::info('Livewire ShowQuotes: Paginator created');

            return view('livewire.show-quotes', [
                'quotes' => $paginator->items(), // Pass items for current page
                'paginator' => $paginator,
            ]);
        } catch (\Exception $e) {
            Log::error('Livewire ShowQuotes: Error fetching quotes: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
             return view('livewire.show-quotes', [
                'quotes' => collect([[
                    'quote' => [
                        'id' => 'error',
                        'body' => 'An error occurred while fetching quotes. Please try again later.',
                        'author' => 'System'
                    ]
                ]]),
                'paginator' => null,
            ]);
        }
    }

    // Reset pagination when any property changes that should trigger a re-fetch
    // public function updated($propertyName)
    // {
    //     $this->resetPage();
    // }
}
