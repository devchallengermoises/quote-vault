<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class Quote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'external_id',
        'body',
        'author'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the users who have favorited this quote.
     *
     * @return BelongsToMany<User>
     */
    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite_quotes')
            ->withTimestamps();
    }

    /**
     * Check if a quote is favorited by a user using cache
     *
     * @param User $user
     * @return bool
     */
    public function isFavoritedBy(User $user): bool
    {
        return Cache::remember(
            "quote_favorite_{$user->id}_{$this->id}",
            300,
            fn() => $this->favoritedBy()->where('users.id', $user->id)->exists()
        );
    }

    /**
     * Toggle the favorite status of this quote for a user.
     *
     * @param User $user The user to toggle the favorite status for
     * @return bool The new favorite status
     */
    public function toggleFavorite(User $user): bool
    {
        $wasFavorited = $this->isFavoritedBy($user);
        
        if ($wasFavorited) {
            $this->favoritedBy()->detach($user->id);
        } else {
            $this->favoritedBy()->attach($user->id);
        }

        // Clear relevant caches
        Cache::forget("quote_favorite_{$user->id}_{$this->id}");
        Cache::forget("user_favorites_{$user->id}");

        return !$wasFavorited;
    }

    /**
     * Scope a query to only include quotes with a specific external ID.
     *
     * @param Builder $query
     * @param string $externalId
     * @return Builder
     */
    public function scopeWithExternalId(Builder $query, string $externalId): Builder
    {
        return $query->where('external_id', $externalId);
    }
}
