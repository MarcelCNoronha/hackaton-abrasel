<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            // token que originou a visita; unico -- um qr_token so pode gerar uma visita.
            $table->foreignId('qr_token_id')->unique()->constrained()->cascadeOnDelete();
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index(['user_id', 'restaurant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
