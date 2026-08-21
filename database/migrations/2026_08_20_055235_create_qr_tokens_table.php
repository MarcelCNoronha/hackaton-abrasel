<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->timestamp('expires_at');
            // gravado no momento em que o token vira uma visita, para impedir reuso.
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_tokens');
    }
};
