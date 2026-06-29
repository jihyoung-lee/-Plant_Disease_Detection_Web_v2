<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prediction_cache_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('url');
            $table->string('hashname')->nullable();
            $table->string('original_name');
            $table->string('crop_name');
            $table->string('sick_name_kor');
            $table->float('confidence')->nullable();
            $table->string('user_opinion')->nullable();
            $table->timestamps();
        });
    }

};
