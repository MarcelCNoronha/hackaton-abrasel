<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            // review que disparou a emissao -- garante rastreabilidade e 1 cupom por avaliacao dentro da campanha.
            $table->foreignId('review_id')->nullable()->constrained()->nullOnDelete();

            $table->string('code')->unique();
            $table->timestamp('issued_at');
            $table->timestamp('expires_at');

            // 'available' | 'used' | 'expired' | 'cancelled'
            $table->string('status')->default('available');

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
