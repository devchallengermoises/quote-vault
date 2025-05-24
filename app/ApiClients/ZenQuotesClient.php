<?php

namespace App\ApiClients;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ZenQuotesClient
{
    private string $baseUrl = 'https://zenquotes.io/api/';
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10.0,
            'connect_timeout' => 5.0,
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'QuoteVault/1.0 (https://github.com/yourusername/quote-vault)'
            ]
        ]);
    }

    private function makeRequest(string $endpoint): array
    {
        try {
            Log::info('Making request to ZenQuotes API', ['endpoint' => $endpoint]);
            
            $response = $this->client->get($endpoint);
            $statusCode = $response->getStatusCode();
            $contents = $response->getBody()->getContents();
            
            Log::info('ZenQuotes API response status', [
                'status' => $statusCode,
                'response_length' => strlen($contents)
            ]);
            
            if ($statusCode !== 200) {
                Log::error("ZenQuotes API error: HTTP Code {$statusCode}", [
                    'response' => $contents
                ]);
                throw new \RuntimeException("Failed to fetch quotes from ZenQuotes API. HTTP Code: {$statusCode}");
            }

            if (empty($contents)) {
                Log::error("ZenQuotes API error: Empty response received");
                throw new \RuntimeException('Empty response received from ZenQuotes API');
            }

            $data = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("ZenQuotes API error: Invalid JSON response", [
                    'response' => $contents,
                    'json_error' => json_last_error_msg()
                ]);
                throw new \RuntimeException('Invalid JSON response from ZenQuotes API: ' . json_last_error_msg());
            }

            if (!is_array($data) || empty($data)) {
                Log::error("ZenQuotes API error: Invalid response format", [
                    'data' => $data
                ]);
                throw new \RuntimeException('Invalid response format from ZenQuotes API: Empty or invalid data');
            }

            // Validate required fields in each quote
            foreach ($data as $quote) {
                $requiredFields = ['q', 'a'];
                foreach ($requiredFields as $field) {
                    if (!isset($quote[$field]) || empty($quote[$field])) {
                        Log::error("ZenQuotes API error: Missing required field in quote", [
                            'field' => $field,
                            'quote' => $quote
                        ]);
                        throw new \RuntimeException("Invalid quote data: Missing required field '{$field}'");
                    }
                }
            }

            Log::info('Successfully received quotes from ZenQuotes API', ['count' => count($data)]);
            return $data;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            Log::error("ZenQuotes API connection error: " . $e->getMessage());
            throw new \RuntimeException('Could not connect to ZenQuotes API: ' . $e->getMessage());
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error("ZenQuotes API request error: " . $e->getMessage());
            throw new \RuntimeException('Error making request to ZenQuotes API: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error("ZenQuotes API unexpected error: " . $e->getMessage());
            throw new \RuntimeException('Unexpected error while fetching quotes: ' . $e->getMessage());
        }
    }

    public function getRandomQuotes(int $count = 50): Collection
    {
        Log::info('Fetching random quotes from ZenQuotes', ['count' => $count]);
        
        try {
            $quotes = $this->makeRequest('quotes');
            
            // Transform the quotes to match our application's format
            $transformedQuotes = collect($quotes)->map(function ($quote) {
                // Generate a consistent ID based on the quote content
                $id = hash('xxh3', $quote['q'] . $quote['a']);
                
                return [
                    'quote' => [
                        'id' => $id,
                        'body' => $quote['q'],
                        'author' => $quote['a']
                    ]
                ];
            });

            // If we need fewer quotes than what we got, take a random sample
            if ($count < $transformedQuotes->count()) {
                $transformedQuotes = $transformedQuotes->random($count);
            }

            Log::info('Successfully transformed quotes', ['count' => $transformedQuotes->count()]);
            return $transformedQuotes;
        } catch (\Exception $e) {
            Log::error("Failed to fetch quotes from ZenQuotes: " . $e->getMessage());
            throw $e;
        }
    }
} 