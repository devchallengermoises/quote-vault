<?php

namespace App\Repositories;

use App\Models\Quote;

/**
 * Repository class for managing quote data
 * 
 * @package App\Repositories
 */
class QuoteRepository
{
    /**
     * Save or update a quote in the database
     * 
     * @param array $quoteData The quote data from the API
     * @return Quote The saved or updated quote
     */
    public function save(array $quoteData): Quote
    {
        return Quote::updateOrCreate(
            ['external_id' => $quoteData['quote']['id']],
            [
                'body' => $quoteData['quote']['body'],
                'author' => $quoteData['quote']['author']
            ]
        );
    }
} 