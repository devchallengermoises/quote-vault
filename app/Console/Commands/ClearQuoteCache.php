<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearQuoteCache extends Command
{
    protected $signature = 'quotes:clear-cache';
    protected $description = 'Clear the quote of the day cache';

    public function handle()
    {
        Cache::forget('quote_of_the_day');
        $this->info('Quote cache cleared successfully.');
    }
} 