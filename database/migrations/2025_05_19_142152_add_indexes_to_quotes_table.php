<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->index('external_id');
            $table->index('created_at');
        });

        Schema::table('favorite_quotes', function (Blueprint $table) {
            $table->index(['user_id', 'quote_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['external_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('favorite_quotes', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'quote_id']);
            $table->dropIndex(['created_at']);
        });
    }
}; 