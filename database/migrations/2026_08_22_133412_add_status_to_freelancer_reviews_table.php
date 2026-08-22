<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freelancer_reviews', function (Blueprint $table) {
            // O dono cria a avaliacao, mas ela so' fica visivel pra outros donos (e conta pra
            // media) depois que o proprio trabalhador confirma que o vinculo foi real -- evita
            // que alguem seja avaliado (bem ou mal) por uma contratacao que nunca aconteceu.
            $table->string('status')->default('pending_approval')->after('freelancer_profile_id');
        });
    }

    public function down(): void
    {
        Schema::table('freelancer_reviews', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
