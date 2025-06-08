<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

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
     * Toggle the favorite status of this quote for a user.
     *
     * @param User $user The user to toggle the favorite status for
     * @return void
     */
    public function toggleFavorite(User $user): void
    {
        $this->favoritedBy()->toggle($user);
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
