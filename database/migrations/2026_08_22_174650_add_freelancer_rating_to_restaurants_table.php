<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // Separado de average_rating/reviews_count (avaliacoes de clientes, publicas) --
            // essa nota so' e' exibida pra outros freelancers, nunca no perfil publico nem
            // pro proprio dono (ver docs do modulo de empregabilidade).
            $table->decimal('freelancer_average_rating', 3, 2)->default(0)->after('reviews_count');
            $table->unsignedInteger('freelancer_reviews_count')->default(0)->after('freelancer_average_rating');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn(['freelancer_average_rating', 'freelancer_reviews_count']);
        });
    }
};
