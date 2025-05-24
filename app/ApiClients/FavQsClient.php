<?php

namespace App\ApiClients;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FavQsClient
{
    private string $baseUrl = 'https://favqs.com/api/';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 5.0,
            'connect_timeout' => 3.0,
            'http_errors' => false,
            'headers' => [
                'Authorization' => 'Token token="' . env('FAVQS_API_KEY', '') . '"',
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]
        ]);
    }

    private function makeRequest(string $endpoint): array
    {
        try {
            Log::info('Making request to FavQs API', ['endpoint' => $endpoint]);
            
            $response = $this->client->get($endpoint);
            Log::info('FavQs API response status', ['status' => $response->getStatusCode()]);
            
            if ($response->getStatusCode() !== 200) {
                Log::error("FavQs API error: HTTP Code {$response->getStatusCode()}", [
                    'response' => $response->getBody()->getContents()
                ]);
                throw new \RuntimeException("Failed to fetch quote from FavQs API. HTTP Code: {$response->getStatusCode()}");
            }

            $data = json_decode($response->getBody()->getContents(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("FavQs API error: Invalid JSON response", [
                    'response' => $response->getBody()->getContents()
                ]);
                throw new \RuntimeException('Invalid JSON response from FavQs API');
            }

            if (!isset($data['quote'])) {
                Log::error("FavQs API error: Missing quote data in response", [
                    'data' => $data
                ]);
                throw new \RuntimeException('Invalid response format from FavQs API');
            }

            Log::info('Successfully received quote from FavQs API', ['data' => $data]);
            return $data;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error("FavQs API connection error: " . $e->getMessage());
            throw new \RuntimeException('Could not connect to FavQs API');
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error("FavQs API request error: " . $e->getMessage());
            throw new \RuntimeException('Error making request to FavQs API');
        } catch (\Exception $e) {
            Log::error("FavQs API unexpected error: " . $e->getMessage());
            throw new \RuntimeException('Unexpected error while fetching quote');
        }
    }

    public function getQuoteOfTheDay(): array
    {
        return $this->makeRequest('qotd');
    }

    public function getRandomQuote(): array
    {
        return $this->makeRequest('qotd');
    }

    public function getRandomQuotes(int $count): Collection
    {
        Log::info('Fetching random quotes', ['count' => $count]);
        
        $quotes = collect();
        
        for ($i = 0; $i < $count; $i++) {
            try {
                $quote = $this->getRandomQuote();
                if ($quote) {
                    $quotes->push($quote);
                    Log::info('Successfully fetched quote', ['index' => $i, 'quote' => $quote]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to fetch random quote {$i}: " . $e->getMessage());
            }
        }

        Log::info('Finished fetching random quotes', ['count' => $quotes->count()]);
        return $quotes;
    }
} 