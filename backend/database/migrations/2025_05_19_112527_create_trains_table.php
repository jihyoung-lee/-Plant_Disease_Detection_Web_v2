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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->string('hashname')->nullable();
            $table->string('originalname');
            $table->string('cropName');
            $table->string('sickNameKor');
            $table->float('confidence')->nullable();
            $table->string('userOpinion')->nullable();
            $table->timestamps();
        });
    }

};
