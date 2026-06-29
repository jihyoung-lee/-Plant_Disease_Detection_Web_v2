<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trains', function (Blueprint $table) {
            $table->foreignId('prediction_cache_id')
                ->nullable()
                ->constrained('prediction_caches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trains', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prediction_cache_id');
        });
    }
};
