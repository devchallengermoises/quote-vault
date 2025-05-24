<?php

namespace Tests\Feature;

use App\ApiClients\FavQsClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FavQsClientTest extends TestCase
{
    public function test_get_quote_of_the_day()
    {
        Http::fake([
            'https://favqs.com/api/qotd' => Http::response([
                'qotd_date' => '2025-05-20T00:00:00.000+00:00',
                'quote' => [
                    'id' => 62062,
                    'body' => 'Our past is not the thing that matters so much in this world as what we intend to do with our future.',
                    'author' => 'Shoghi Effendi'
                ]
            ], 200)
        ]);

        $client = app(FavQsClient::class);
        $response = $client->getQuoteOfTheDay();

        $this->assertEquals('2025-05-20T00:00:00.000+00:00', $response['qotd_date']);
        $this->assertEquals(62062, $response['quote']['id']);
        $this->assertEquals('Our past is not the thing that matters so much in this world as what we intend to do with our future.', $response['quote']['body']);
        $this->assertEquals('Shoghi Effendi', $response['quote']['author']);
    }
} 