<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // unico -- uma visita valida gera no maximo uma avaliacao, impede review em duplicidade.
            $table->foreignId('visit_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('food_rating')->nullable();
            $table->unsignedTinyInteger('service_rating')->nullable();
            $table->unsignedTinyInteger('ambience_rating')->nullable();
            $table->unsignedTinyInteger('value_rating')->nullable();
            $table->text('comment')->nullable();

            // 'published' | 'hidden' | 'flagged'
            $table->string('status')->default('published');

            $table->timestamps();

            $table->index(['restaurant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
