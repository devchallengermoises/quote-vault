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

/**
 * Service class for managing quotes
 * 
 * @package App\Services
 */
class QuoteService
{
    private const CACHE_KEY = 'all_quotes';
    private const CACHE_TTL = 86400; // 24 hours in seconds
    private const FAVORITE_CACHE_TTL = 300; // 5 minutes in seconds

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
     * Get and cache 50 unique quotes from the API using Redis
     *
     * @param int $count
     * @return SupportCollection
     */
    public function getRandomQuotes(int $count): SupportCollection
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
                return $this->transformQuotes($quotes);
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

            return $this->transformQuotes($quotes);
        } catch (\Exception $e) {
            Log::error('Error fetching quotes from API: ' . $e->getMessage());
            
            // If API fails, try to get any available quotes from Redis
            if (Redis::exists($redisKey)) {
                $cached = Redis::smembers($redisKey);
                foreach ($cached as $item) {
                    $quotes->push(json_decode($item, true));
                }
                return $this->transformQuotes($quotes);
            }
            
            throw $e;
        }
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

        $cacheKey = "quote_favorite_{$quoteId}_" . auth()->id();
        
        return Cache::remember($cacheKey, self::FAVORITE_CACHE_TTL, function () use ($quoteId) {
            return auth()->user()->favoriteQuotes()
                ->where('external_id', $quoteId)
                ->exists();
        });
    }

    /**
     * Clear the quotes cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Clear the favorite status cache for a specific quote
     *
     * @param string $quoteId The quote ID to clear cache for
     * @return void
     */
    public function clearFavoriteCache(string $quoteId): void
    {
        if (auth()->user()) {
            Cache::forget("quote_favorite_{$quoteId}_" . auth()->id());
        }
    }

    /**
     * Get the user's favorite quotes from Redis cache or DB
     *
     * @return SupportCollection
     */
    public function getFavoriteQuotes(): SupportCollection
    {
        $user = auth()->user();
        if (!$user) {
            return collect([]);
        }
        $redisKey = 'user:' . $user->id . ':favorites';
        $favorites = collect();
        if (Redis::exists($redisKey)) {
            foreach (Redis::smembers($redisKey) as $item) {
                $favorites->push(json_decode($item, true));
            }
            return $favorites;
        }
        $dbFavorites = $user->favoriteQuotes()->get()->map(function ($quote) {
            $data = [
                'id' => $quote->external_id,
                'external_id' => $quote->external_id,
                'body' => $quote->body,
                'author' => $quote->author,
                'is_favorite' => true,
                'is_long' => strlen($quote->body) > 200
            ];
            return $data;
        });
        foreach ($dbFavorites as $fav) {
            Redis::sadd($redisKey, json_encode($fav));
        }
        Redis::expire($redisKey, self::FAVORITE_CACHE_TTL);
        return $dbFavorites;
    }

    /**
     * Toggle the favorite status of a quote for the current user and update Redis cache
     *
     * @param string $quoteId The external_id of the quote
     * @return bool The new favorite status
     */
    public function toggleFavorite(string $quoteId): bool
    {
        $user = auth()->user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }
        $quote = Quote::where('external_id', $quoteId)->first();
        $isFavorite = false;
        if (!$quote) {
            // Buscar la cita en cache/redis
            $allCached = $this->getRandomQuotes(50)->merge($this->getFavoriteQuotes());
            $quoteData = $allCached->firstWhere('id', $quoteId) ?? $allCached->firstWhere('external_id', $quoteId);
            if (!$quoteData) {
                throw new \Exception('Quote not found');
            }
            // Solo crear en DB si se va a agregar a favoritos
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
        $redisKey = 'user:' . $user->id . ':favorites';
        $favData = [
            'id' => $quote->external_id,
            'external_id' => $quote->external_id,
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
        $this->clearFavoriteCache($quoteId);
        return $isFavorite;
    }
} 