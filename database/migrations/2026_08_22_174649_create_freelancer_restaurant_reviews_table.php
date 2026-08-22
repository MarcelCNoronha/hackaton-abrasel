<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Espelha freelancer_reviews (dono avalia freelancer), so que na direcao contraria --
        // freelancer avalia o restaurante que o contratou. 1 avaliacao por hire_request, so'
        // do lado do freelancer (nao conflita com a review do dono na mesma contratacao).
        Schema::create('freelancer_restaurant_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hire_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_restaurant_reviews');
    }
};
