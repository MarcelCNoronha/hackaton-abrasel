<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            // cover_photo_path e' a foto pequena usada na listagem/card; banner_photo_path e'
            // a imagem larga do topo da pagina do restaurante -- fotos distintas de proposito.
            $table->string('banner_photo_path')->nullable()->after('cover_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('banner_photo_path');
        });
    }
};
