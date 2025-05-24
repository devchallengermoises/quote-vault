<?php

namespace Tests\Unit;

use App\Models\Quote;
use App\Repositories\QuoteRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_creates_or_updates_quote()
    {
        $repo = app(QuoteRepository::class);
        $data = [
            'quote' => [
                'id' => 999,
                'body' => 'Unit test quote',
                'author' => 'Unit Tester'
            ]
        ];
        $repo->save($data);
        $this->assertDatabaseHas('quotes', [
            'external_id' => 999,
            'body' => 'Unit test quote',
            'author' => 'Unit Tester'
        ]);

        // Update
        $data['quote']['body'] = 'Updated body';
        $repo->save($data);
        $this->assertDatabaseHas('quotes', [
            'external_id' => 999,
            'body' => 'Updated body',
        ]);
    }
} 