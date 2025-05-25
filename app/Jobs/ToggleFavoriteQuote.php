<?php

namespace App\Jobs;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ToggleFavoriteQuote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;
    private string $quoteId;
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $quoteId)
    {
        $this->user = $user;
        $this->quoteId = $quoteId;
    }

    /**
     * Execute the job.
     */
    public function handle(): bool
    {
        $quote = Quote::where('external_id', $this->quoteId)->first();
        $isFavorite = false;

        if (!$quote) {
            // Find quote in cache
            $allCached = collect();
            if (Redis::exists('quotes:all')) {
                $allCached = collect(json_decode(Redis::get('quotes:all'), true));
            }
            
            $quoteData = $allCached->firstWhere('id', $this->quoteId) ?? $allCached->firstWhere('external_id', $this->quoteId);
            
            if (!$quoteData) {
                throw new \Exception('Quote not found');
            }

            // Create quote in DB
            $quote = Quote::create([
                'external_id' => $this->quoteId,
                'body' => $quoteData['body'],
                'author' => $quoteData['author']
            ]);
            
            $this->user->favoriteQuotes()->attach($quote->id);
            $isFavorite = true;
        } else {
            $isFavorite = $this->user->favoriteQuotes()->where('quotes.id', $quote->id)->exists();
            
            if ($isFavorite) {
                $this->user->favoriteQuotes()->detach($quote->id);
                $isFavorite = false;
            } else {
                $this->user->favoriteQuotes()->attach($quote->id);
                $isFavorite = true;
            }
        }

        // Update Redis cache
        $redisKey = "user:{$this->user->id}:favorites";
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
        
        Redis::expire($redisKey, self::CACHE_TTL);
        
        // Clear all caches
        $this->clearAllCaches();
        
        // Clear Redis quotes cache
        Redis::del('quotes:all');

        return $isFavorite;
    }

    /**
     * Clear all caches related to the quote
     */
    private function clearAllCaches(): void
    {
        $userId = $this->user->id;
        
        // Clear favorite status cache
        Cache::forget("quote_favorite_{$userId}_{$this->quoteId}");
        
        // Clear user favorites cache
        Cache::forget("quotes:favorites:{$userId}:v1");
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
        Redis::del('quotes:all');
    }
} 