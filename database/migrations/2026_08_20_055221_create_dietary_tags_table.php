<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dietary_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            // 'diet' = escolha/preferência (vegetariano, vegano...);
            // 'allergen' = restrição médica (alergia/intolerância) que a busca deve levar mais a sério.
            $table->string('kind')->default('diet');
            $table->string('icon')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dietary_tags');
    }
};
