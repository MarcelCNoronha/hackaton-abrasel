<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hire_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_profile_id')->constrained()->cascadeOnDelete();
            // nullOnDelete (nao cascadeOnDelete) -- se o dono que pediu a contratacao apagar a
            // propria conta, o pedido/contratacao em si (e a avaliacao presa a ele) continuam
            // existindo, so' perde o vinculo com quem pediu. Mesma correcao ja aplicada em
            // reviews.user_id/coupons.user_id neste mes.
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['freelancer_profile_id', 'status']);
            $table->index('restaurant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hire_requests');
    }
};
