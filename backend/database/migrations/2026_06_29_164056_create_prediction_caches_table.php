<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_caches', function (Blueprint $table) {
            $table->id();

            $table->string('hashname', 64)->unique();
            $table->string('crop_name');
            $table->string('sick_name');
            $table->float('confidence');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_caches');
    }
};
