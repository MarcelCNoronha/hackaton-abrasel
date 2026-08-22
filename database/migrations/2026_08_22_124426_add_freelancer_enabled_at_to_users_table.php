<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Fora do #[Fillable] de proposito (mesmo tratamento de restaurants.claimed_at) --
            // so' um admin pode liberar o modulo de empregabilidade pra alguem, nunca a
            // propria pessoa via update em massa do proprio perfil.
            $table->timestamp('freelancer_enabled_at')->nullable()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('freelancer_enabled_at');
        });
    }
};
