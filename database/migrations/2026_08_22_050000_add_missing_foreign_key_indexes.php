<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Postgres, ao contrario do MySQL, nao cria um indice automaticamente pra toda foreign key --
// essas colunas ficavam sem nenhum indice, forcando sequential scan em qualquer join/lookup
// por elas (ex.: carregar o cardapio de um restaurante, ou o historico de reivindicacao).
// restaurants.is_active tambem entra aqui por ser filtrado em praticamente toda query publica
// (Discovery, Sitemap).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_claims', function (Blueprint $table) {
            $table->index('restaurant_id');
            $table->index('user_id');
        });

        Schema::table('restaurant_photos', function (Blueprint $table) {
            $table->index('restaurant_id');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->index('restaurant_id');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->index('menu_id');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->index('menu_category_id');
        });

        Schema::table('menu_item_photos', function (Blueprint $table) {
            $table->index('menu_item_id');
        });

        Schema::table('review_photos', function (Blueprint $table) {
            $table->index('review_id');
        });

        Schema::table('review_replies', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->index('redeemed_by');
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_claims', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('restaurant_photos', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id']);
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['restaurant_id']);
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropIndex(['menu_id']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['menu_category_id']);
        });

        Schema::table('menu_item_photos', function (Blueprint $table) {
            $table->dropIndex(['menu_item_id']);
        });

        Schema::table('review_photos', function (Blueprint $table) {
            $table->dropIndex(['review_id']);
        });

        Schema::table('review_replies', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropIndex(['redeemed_by']);
        });

        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
    }
};
