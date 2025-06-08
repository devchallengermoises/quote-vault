<?php

namespace App\Services;

use App\ApiClients\FavQsClient;
use App\Models\Quote;
use App\Repositories\QuoteRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

/**
 * Service class for managing quotes
 * 
 * @package App\Services
 */
class QuoteService
{
    /**
     * Create a new QuoteService instance
     * 
     * @param FavQsClient $apiClient The FavQs API client
     * @param QuoteRepository $repository The quote repository
     */
    public function __construct(
        private readonly FavQsClient $apiClient,
        private readonly QuoteRepository $repository
    ) {}

    /**
     * Get the quote repository
     * 
     * @return QuoteRepository
     */
    public function getQuoteRepository(): QuoteRepository
    {
        return $this->repository;
    }

    /**
     * Get the quote of the day
     * 
     * @return Quote|null The quote of the day or null if not available
     */
    public function getQuoteOfTheDay(): ?Quote
    {
        try {
            $cachedQuote = Cache::get('quote_of_the_day');
            if ($cachedQuote) {
                return $cachedQuote;
            }

            $quoteData = $this->apiClient->getQuoteOfTheDay();
            if (!isset($quoteData['quote']) || !isset($quoteData['quote']['id']) || !isset($quoteData['quote']['body']) || !isset($quoteData['quote']['author'])) {
                \Log::error('Invalid quote of the day data format: ' . json_encode($quoteData));
                return null;
            }

            $quote = $this->repository->save($quoteData);
            Cache::put('quote_of_the_day', $quote, now()->endOfDay());
            return $quote;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch quote of the day: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a random quote
     * 
     * @return array|null A random quote or null if not available
     */
    public function getRandomQuote(): ?array
    {
        try {
            $quoteData = $this->apiClient->getRandomQuote();
            if (!isset($quoteData['quote']) || !isset($quoteData['quote']['id']) || !isset($quoteData['quote']['body']) || !isset($quoteData['quote']['author'])) {
                \Log::error('Invalid random quote data format: ' . json_encode($quoteData));
                return null;
            }
            return $quoteData;
        } catch (\Exception $e) {
            \Log::error('Failed to fetch random quote: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get random quotes from the API.
     *
     * @param int $count The number of quotes to get.
     * @return SupportCollection A collection of random quotes.
     */
    public function getRandomQuotes(int $count): SupportCollection
    {
        try {
            $quotes = $this->apiClient->getRandomQuotes($count);
            return $quotes->filter(function ($quote) {
                return isset($quote['quote']) && 
                       isset($quote['quote']['id']) && 
                       isset($quote['quote']['body']) && 
                       isset($quote['quote']['author']);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to fetch random quotes: ' . $e->getMessage());
            return collect([]);
        }
    }
} 