<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class ClearQuotesCache extends Command
{
    protected $signature = 'quotes:clear-cache';
    protected $description = 'Clear the quotes cache from Redis';

    public function handle()
    {
        $this->info('Clearing quotes cache...');
        
        $redisKey = 'quotes:all';
        if (Redis::exists($redisKey)) {
            Redis::del($redisKey);
            $this->info('Quotes cache cleared successfully.');
        } else {
            $this->info('No quotes cache found.');
        }

        return Command::SUCCESS;
    }
} 