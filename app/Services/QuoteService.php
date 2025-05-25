<?php

namespace App\Services;

use App\ApiClients\ZenQuotesClient;
use App\Models\Quote;
use App\Repositories\QuoteRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ToggleFavoriteQuote;

/**
 * Service class for managing quotes
 * 
 * @package App\Services
 */
class QuoteService
{
    private const CACHE_VERSION = 'v1';
    private const CACHE_TTL = 300; // 5 minutes
    private const FAVORITE_CACHE_TTL = 3600; // 1 hour

    /**
     * Create a new QuoteService instance
     * 
     * @param ZenQuotesClient $apiClient The ZenQuotes API client
     * @param QuoteRepository $repository The quote repository
     */
    public function __construct(
        private readonly ZenQuotesClient $apiClient,
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
     * Get the cache key for a specific resource
     */
    private function getCacheKey(string $type, string $id = null): string
    {
        return "quotes:{$type}" . ($id ? ":{$id}" : '') . ':' . self::CACHE_VERSION;
    }

    /**
     * Get random quotes
     */
    public function getRandomQuotes(int $count = 10): \Illuminate\Support\Collection
    {
        $cacheKey = $this->getCacheKey('random', $count);
        
        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($count) {
                return $this->fetchQuotesFromApi($count);
            }
        );
    }

    /**
     * Transform quotes from API format to application format
     *
     * @param SupportCollection $quotes The quotes from the API
     * @return SupportCollection The transformed quotes
     */
    private function transformQuotes(SupportCollection $quotes): SupportCollection
    {
        return $quotes->map(function ($quote) {
            $quoteData = $quote['quote'];
            return [
                'id' => $quoteData['id'],
                'body' => $quoteData['body'],
                'author' => $quoteData['author'],
                'is_favorite' => $this->isQuoteFavorited($quoteData['id']),
                'is_long' => strlen($quoteData['body']) > 200
            ];
        });
    }

    /**
     * Check if a quote is favorited by the current user
     *
     * @param string $quoteId The quote ID to check
     * @return bool Whether the quote is favorited
     */
    private function isQuoteFavorited(string $quoteId): bool
    {
        if (!auth()->user()) {
            return false;
        }

        $userId = auth()->id();
        $cacheKey = "quote_favorite_{$userId}_{$quoteId}";
        
        return Cache::remember($cacheKey, self::FAVORITE_CACHE_TTL, function () use ($quoteId) {
            return auth()->user()->favoriteQuotes()
                ->where('external_id', $quoteId)
                ->exists();
        });
    }

    /**
     * Clear all caches related to a quote
     */
    public function clearAllCaches(string $quoteId): void
    {
        if ($user = auth()->user()) {
            $userId = $user->id;
            
            // Clear favorite status cache
            Cache::forget("quote_favorite_{$userId}_{$quoteId}");
            
            // Clear user favorites cache
            Cache::forget($this->getCacheKey('favorites', $userId));
            Cache::forget("user_favorite_ids_{$userId}");
            
            // Clear pagination caches
            Cache::forget("favorites_user_{$userId}_page_*");
            Cache::forget("quotes_count_favorites_user_{$userId}");
            
            // Clear random quotes cache for this user
            Cache::forget("quotes_all_user_{$userId}_page_*");
            Cache::forget("quotes_count_all_user_{$userId}");
            
            // Clear total quotes count cache
            Cache::forget('total_quotes_count');
            
            // Clear Redis cache
            Redis::del("user:{$userId}:favorites");
            
            // Clear all quotes cache
            Cache::forget($this->getCacheKey('random'));
        }
    }

    /**
     * Toggle the favorite status of a quote for the current user
     */
    public function toggleFavorite(string $quoteId): bool
    {
        $user = auth()->user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        // Check current favorite status
        $quote = Quote::where('external_id', $quoteId)->first();
        $isFavorite = false;

        if (!$quote) {
            // Find quote in cache
            $allCached = collect();
            if (Redis::exists('quotes:all')) {
                $allCached = collect(json_decode(Redis::get('quotes:all'), true));
            }
            
            $quoteData = $allCached->firstWhere('id', $quoteId) ?? $allCached->firstWhere('external_id', $quoteId);
            
            if (!$quoteData) {
                throw new \Exception('Quote not found');
            }

            // Create quote in DB
            $quote = Quote::create([
                'external_id' => $quoteId,
                'body' => $quoteData['body'],
                'author' => $quoteData['author']
            ]);
            
            $user->favoriteQuotes()->attach($quote->id);
            $isFavorite = true;
        } else {
            $isFavorite = $user->favoriteQuotes()->where('quotes.id', $quote->id)->exists();
            
            if ($isFavorite) {
                $user->favoriteQuotes()->detach($quote->id);
                $isFavorite = false;
            } else {
                $user->favoriteQuotes()->attach($quote->id);
                $isFavorite = true;
            }
        }

        // Update Redis cache
        $redisKey = "user:{$user->id}:favorites";
        $favData = [
            'id' => $quote->external_id,
            'body' => $quote->body,
            'author' => $quote->author,
            'is_favorite' => $isFavorite,
            'is_long' => strlen($quote->body) > 200
        ];

        if ($isFavorite) {
            Redis::sadd($redisKey, json_encode($favData));
        } else {
            Redis::srem($redisKey, json_encode($favData));
        }
        
        Redis::expire($redisKey, self::FAVORITE_CACHE_TTL);
        
        // Clear all caches
        $this->clearAllCaches($quoteId);
        
        // Clear Redis quotes cache
        Redis::del('quotes:all');

        // Dispatch job to handle background tasks
        dispatch(new ToggleFavoriteQuote($user, $quoteId))->onQueue('favorites');

        // Return the opposite of current state for UI update
        // This is because we want to show the new state immediately
        return !$isFavorite;
    }

    /**
     * Get the user's favorite quotes
     */
    public function getFavoriteQuotes(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if (!$user) {
            return collect([]);
        }

        $cacheKey = $this->getCacheKey('favorites', $user->id);
        
        return Cache::remember(
            $cacheKey,
            self::FAVORITE_CACHE_TTL,
            function () use ($user) {
                return $user->favoriteQuotes()
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($quote) {
                        return [
                            'id' => $quote->external_id,
                            'body' => $quote->body,
                            'author' => $quote->author,
                            'is_favorite' => true,
                            'is_long' => strlen($quote->body) > 200,
                            'created_at' => $quote->created_at
                        ];
                    });
            }
        );
    }

    /**
     * Fetch quotes from the API
     *
     * @param int $count The number of quotes to fetch
     * @return SupportCollection The fetched quotes
     */
    private function fetchQuotesFromApi(int $count): SupportCollection
    {
        $redisKey = 'quotes:all';
        $quotes = collect();

        // Try to get quotes from Redis first
        if (Redis::exists($redisKey)) {
            $cached = Redis::smembers($redisKey);
            if (count($cached) >= $count) {
                foreach (array_slice($cached, 0, $count) as $item) {
                    $quotes->push(json_decode($item, true));
                }
                $quotes = $this->transformQuotes($quotes);
                
                // Filter out favorites if user is logged in
                if (auth()->check()) {
                    $favoriteIds = $this->getUserFavoriteIds();
                    $quotes = $quotes->filter(function ($quote) use ($favoriteIds) {
                        return !in_array($quote['id'], $favoriteIds);
                    });
                }
                
                return $quotes;
            }
        }

        // If we don't have enough quotes in Redis, fetch from API
        try {
            $apiQuotes = $this->apiClient->getRandomQuotes($count);
            foreach ($apiQuotes as $quote) {
                // Normalize data access to handle both API and Redis formats
                $body = $quote['q'] ?? $quote['body'] ?? $quote['quote']['body'] ?? null;
                $author = $quote['a'] ?? $quote['author'] ?? $quote['quote']['author'] ?? null;
                $id = $quote['id'] ?? $quote['quote']['id'] ?? null;
                
                if (!$body || !$author) {
                    Log::warning('Invalid quote format received', ['quote' => $quote]);
                    continue;
                }

                $externalId = $id ?? hash('xxh3', $body . $author);
                $data = [
                    'quote' => [
                        'id' => $externalId,
                        'body' => $body,
                        'author' => $author
                    ]
                ];

                // Store in Redis
                Redis::sadd($redisKey, json_encode($data));
                $quotes->push($data);
            }

            // Set expiration for the Redis key
            Redis::expire($redisKey, self::CACHE_TTL);

            // Store in database for persistence
            foreach ($quotes as $quoteData) {
                $this->repository->save($quoteData);
            }

            $quotes = $this->transformQuotes($quotes);
            
            // Filter out favorites if user is logged in
            if (auth()->check()) {
                $favoriteIds = $this->getUserFavoriteIds();
                $quotes = $quotes->filter(function ($quote) use ($favoriteIds) {
                    return !in_array($quote['id'], $favoriteIds);
                });
            }

            return $quotes;
        } catch (\Exception $e) {
            Log::error('Error fetching quotes from API: ' . $e->getMessage());
            
            // If API fails, try to get any available quotes from Redis
            if (Redis::exists($redisKey)) {
                $cached = Redis::smembers($redisKey);
                foreach ($cached as $item) {
                    $quotes->push(json_decode($item, true));
                }
                $quotes = $this->transformQuotes($quotes);
                
                // Filter out favorites if user is logged in
                if (auth()->check()) {
                    $favoriteIds = $this->getUserFavoriteIds();
                    $quotes = $quotes->filter(function ($quote) use ($favoriteIds) {
                        return !in_array($quote['id'], $favoriteIds);
                    });
                }
                
                return $quotes;
            }
            
            throw $e;
        }
    }

    /**
     * Get the IDs of user's favorite quotes
     *
     * @return array
     */
    private function getUserFavoriteIds(): array
    {
        if (!auth()->check()) {
            return [];
        }

        $userId = auth()->id();
        $cacheKey = "user_favorite_ids_{$userId}";
        
        return Cache::remember($cacheKey, self::FAVORITE_CACHE_TTL, function () {
            return auth()->user()->favoriteQuotes()
                ->pluck('external_id')
                ->toArray();
        });
    }
} 