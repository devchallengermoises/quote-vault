<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;
use Mockery;

class QuoteTest extends TestCase
{
    private Quote $quote;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->quote = Mockery::mock(Quote::class)->makePartial();
        $this->user = Mockery::mock(User::class)->makePartial();
        
        $this->quote->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $this->user->shouldReceive('getAttribute')->with('id')->andReturn(1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_quote_has_favorited_by_relation()
    {
        // Arrange
        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('withTimestamps')->andReturnSelf();
        
        $this->quote->shouldReceive('belongsToMany')
            ->once()
            ->with(User::class, 'favorite_quotes')
            ->andReturn($relation);

        // Act
        $result = $this->quote->favoritedBy();

        // Assert
        $this->assertInstanceOf(BelongsToMany::class, $result);
    }

    public function test_is_favorited_by_uses_cache()
    {
        // Arrange
        $cacheKey = "quote_favorite_1_1";
        $cacheValue = true;

        $relation = Mockery::mock(BelongsToMany::class);
        $where = Mockery::mock('where');
        $where->shouldReceive('exists')->once()->andReturn(true);
        $relation->shouldReceive('where')->once()->andReturn($where);

        $this->quote->shouldReceive('favoritedBy')
            ->once()
            ->andReturn($relation);

        Cache::shouldReceive('remember')
            ->once()
            ->with($cacheKey, 300, Mockery::type('Closure'))
            ->andReturn($cacheValue);

        // Act
        $result = $this->quote->isFavoritedBy($this->user);

        // Assert
        $this->assertTrue($result);
    }

    public function test_toggle_favorite_handles_favorite_and_unfavorite()
    {
        // Arrange
        $this->quote->shouldReceive('isFavoritedBy')
            ->with($this->user)
            ->andReturn(false, true);

        $relation = Mockery::mock(BelongsToMany::class);
        $relation->shouldReceive('attach')->once()->with($this->user->id);
        $relation->shouldReceive('detach')->once()->with($this->user->id);

        $this->quote->shouldReceive('favoritedBy')
            ->twice()
            ->andReturn($relation);

        Cache::shouldReceive('forget')
            ->with("quote_favorite_1_1")
            ->twice();

        Cache::shouldReceive('forget')
            ->with("user_favorites_1")
            ->twice();

        // Act & Assert - First toggle (favorite)
        $result = $this->quote->toggleFavorite($this->user);
        $this->assertTrue($result);

        // Act & Assert - Second toggle (unfavorite)
        $result = $this->quote->toggleFavorite($this->user);
        $this->assertFalse($result);
    }

    public function test_scope_with_external_id()
    {
        // Arrange
        $query = Mockery::mock('Illuminate\Database\Eloquent\Builder');
        $query->shouldReceive('where')
            ->once()
            ->with('external_id', '123')
            ->andReturnSelf();

        $this->quote->shouldReceive('newQuery')
            ->once()
            ->andReturn($query);

        // Act
        $result = $this->quote->scopeWithExternalId($query, '123');

        // Assert
        $this->assertSame($query, $result);
    }
} 