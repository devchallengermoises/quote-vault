<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_favorite_and_unfavorite_quote_e2e()
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create();
        $this->actingAs($user);

        // Favoritar
        $response = $this->post(route('quotes.toggle-favorite', $quote));
        $response->assertRedirect();
        $this->assertDatabaseHas('favorite_quotes', [
            'user_id' => $user->id,
            'quote_id' => $quote->id
        ]);

        // Desfavoritar
        $response = $this->post(route('quotes.toggle-favorite', $quote));
        $response->assertRedirect();
        $this->assertDatabaseMissing('favorite_quotes', [
            'user_id' => $user->id,
            'quote_id' => $quote->id
        ]);
    }
} 