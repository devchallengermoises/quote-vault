<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

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
        return view('quotes.index');
    }

    /**
     * Display the user's favorite quotes
     * 
     * @return View
     */
    public function favorites(): View
    {
        return view('quotes.favorites');
    }

    /**
     * Toggle the favorite status of a quote
     * 
     * @param string $externalId The external ID of the quote to toggle
     * @return RedirectResponse|JsonResponse
     */
    public function toggleFavorite(string $externalId): RedirectResponse|JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$externalId) {
                Log::warning('Attempted to toggle favorite with empty external ID');
                return request()->wantsJson() 
                    ? response()->json(['error' => 'Invalid quote ID'], 400)
                    : back()->with('error', 'Invalid quote ID');
            }

            // Buscar la cita en la base de datos o crearla si no existe
            $quote = Quote::where('external_id', $externalId)->first();
            
            if (!$quote) {
                // Obtener los datos de la cita del servicio
                $quoteData = $this->quoteService->getRandomQuotes(1)->first();
                if (!$quoteData) {
                    Log::error('Failed to fetch quote data for external ID: ' . $externalId);
                    return request()->wantsJson()
                        ? response()->json(['error' => 'Failed to fetch quote data'], 500)
                        : back()->with('error', 'Failed to fetch quote data');
                }

                // Crear la cita en la base de datos
                $quote = Quote::create([
                    'external_id' => $externalId,
                    'body' => $quoteData['body'],
                    'author' => $quoteData['author']
                ]);
            }
            
            // Verificar si la cita ya está en favoritos
            $isFavorite = $user->favoriteQuotes()->where('quotes.id', $quote->id)->exists();
            
            if ($isFavorite) {
                $user->favoriteQuotes()->detach($quote->id);
                $message = 'Quote removed from favorites!';
                $isFavorite = false;
            } else {
                $user->favoriteQuotes()->attach($quote->id);
                $message = 'Quote added to favorites!';
                $isFavorite = true;
            }

            // Limpiar los caches relevantes
            $this->quoteService->clearFavoriteCache($externalId);
            Cache::forget('user_favorites_' . $user->id);

            if (request()->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'isFavorite' => $isFavorite
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error in QuoteController@toggleFavorite: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'externalId' => $externalId
            ]);
            
            return request()->wantsJson()
                ? response()->json(['error' => 'An error occurred while updating favorites'], 500)
                : back()->with('error', 'An error occurred while updating favorites. Please try again later.');
        }
    }
} 