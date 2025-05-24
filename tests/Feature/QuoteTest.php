<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteService;
use App\Repositories\QuoteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_quote_of_the_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('quotes.index'));
        $response->assertStatus(200);
    }

    public function test_user_can_toggle_favorite_quote()
    {
        $user = User::factory()->create();
        $quote = Quote::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('quotes.toggle-favorite', $quote));
        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue($user->favoriteQuotes->contains($quote));

        $response = $this->post(route('quotes.toggle-favorite', $quote));
        $response->assertRedirect();
        $user->refresh();
        $this->assertFalse($user->favoriteQuotes->contains($quote));
    }

    public function test_quote_service_save_quote()
    {
        $quoteData = [
            'quote' => [
                'id' => 123,
                'body' => 'Test quote body',
                'author' => 'Test Author'
            ]
        ];

        $quoteService = app(QuoteService::class);
        $quoteService->saveQuote($quoteData);

        $this->assertDatabaseHas('quotes', [
            'external_id' => 123,
            'body' => 'Test quote body',
            'author' => 'Test Author'
        ]);
    }

    public function test_quote_repository_save()
    {
        $quoteData = [
            'quote' => [
                'id' => 456,
                'body' => 'Another test quote',
                'author' => 'Another Author'
            ]
        ];

        $quoteRepository = app(QuoteRepository::class);
        $quoteRepository->save($quoteData);

        $this->assertDatabaseHas('quotes', [
            'external_id' => 456,
            'body' => 'Another test quote',
            'author' => 'Another Author'
        ]);
    }
} 