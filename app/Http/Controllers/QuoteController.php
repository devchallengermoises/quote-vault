<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Controller for managing quotes and favorites
 * 
 * @package App\Http\Controllers
 */
class QuoteController extends Controller
{
    /**
     * Create a new QuoteController instance
     * 
     * @param QuoteService $quoteService The quote service
     */
    public function __construct(
        private readonly QuoteService $quoteService
    ) {
    }

    /**
     * Display the quote of the day or a random quote
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            Log::info('Starting to fetch quotes from API');
            
            // Fetch quotes from the API
            $quotes = $this->quoteService->getRandomQuotes(12);
            Log::info('Quotes received from API:', ['quotes' => $quotes]);

            if (empty($quotes)) {
                Log::warning('No quotes received from API');
                throw new \RuntimeException('No quotes received from API');
            }

            // Transform the quotes to the format expected by the view
            $transformedQuotes = collect($quotes)->map(function ($quote) {
                return [
                    'quote' => [
                        'id' => $quote['quote']['id'],
                        'body' => $quote['quote']['body'],
                        'author' => $quote['quote']['author'],
                    ]
                ];
            })->toArray();
            Log::info('Transformed quotes:', ['transformed' => $transformedQuotes]);

            // Create a manual paginator
            $page = request()->get('page', 1);
            $perPage = 12;
            $paginator = new LengthAwarePaginator(
                $transformedQuotes,
                count($transformedQuotes),
                $perPage,
                $page,
                ['path' => request()->url()]
            );

            return view('quotes.index', [
                'quotes' => $transformedQuotes,
                'paginator' => $paginator,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in QuoteController@index: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return view('quotes.index', [
                'quotes' => [[
                    'quote' => [
                        'id' => 'error',
                        'body' => 'An error occurred while fetching quotes. Please try again later.',
                        'author' => 'System'
                    ]
                ]],
                'paginator' => null,
            ]);
        }
    }

    /**
     * Display the user's favorite quotes
     * 
     * @return View|RedirectResponse
     */
    public function favorites(): View|RedirectResponse
    {
        // We will now handle fetching and pagination in the Livewire component
        return view('quotes.favorites');
    }

    /**
     * Toggle the favorite status of a quote
     * 
     * @param string $externalId The external ID of the quote to toggle
     * @return RedirectResponse
     */
    public function toggleFavorite(string $externalId): RedirectResponse
    {
        try {
            $user = auth()->user();
            
            if (!$externalId) {
                Log::warning('Attempted to toggle favorite with empty external ID');
                return back()->with('error', 'Invalid quote ID');
            }

            $quote = Quote::where('external_id', $externalId)->first();
            
            if (!$quote) {
                $quoteData = $this->quoteService->getRandomQuote();
                if (!$quoteData) {
                    Log::error('Failed to fetch quote data for external ID: ' . $externalId);
                    return back()->with('error', 'Failed to fetch quote data');
                }
                $quote = $this->quoteService->getQuoteRepository()->save($quoteData);
            }
            
            if ($user->favoriteQuotes()->where('quotes.id', $quote->id)->exists()) {
                $user->favoriteQuotes()->detach($quote->id);
                $message = 'Quote removed from favorites!';
            } else {
                $user->favoriteQuotes()->attach($quote->id);
                $message = 'Quote added to favorites!';
            }

            // Clear the favorites cache for this user
            Cache::forget('user_favorites_' . $user->id);

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error in QuoteController@toggleFavorite: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'externalId' => $externalId
            ]);
            return back()->with('error', 'An error occurred while updating favorites. Please try again later.');
        }
    }
} 