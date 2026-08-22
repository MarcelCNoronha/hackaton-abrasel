<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// reviews.user_id e coupons.user_id eram cascadeOnDelete -- uma avaliacao publica (que o
// average_rating/reviews_count do restaurante dependem, ver ReviewObserver) ou o registro de
// um cupom desaparecia por completo no instante em que quem a criou apagasse a propria conta.
// restaurant_claims.reviewed_by e coupon_campaigns.proposed_by ja usavam nullOnDelete pra essa
// mesma situacao -- aqui alinha reviews/coupons ao mesmo padrao: a conta some, o conteudo (nota,
// comentario, cupom emitido) fica, so o vinculo com o usuario e' que vira null.
//
// visits.user_id tambem precisa entrar aqui: reviews.visit_id e' unique+cascadeOnDelete, entao
// so' desligar reviews.user_id nao bastava -- apagar o usuario ainda apagava a Visit (dono
// original) e isso cascateava e destruia a Review por tabela, mesmo com o fix acima.
//
// restaurant_owners.user_id continua cascadeOnDelete de proposito: e' uma tabela pivot pura
// (usuario <-> restaurante), sem conteudo pra preservar -- uma linha com user_id nulo ali seria
// so' lixo acumulando, nao um registro anonimizado com valor.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
        Schema::table('visits', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
        Schema::table('visits', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
