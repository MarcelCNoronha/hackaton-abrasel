<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mesma forma de business_hours (weekday 0-6, opens_at/closes_at, 1 flag de
        // ausencia) -- so' relevante quando freelancer_profiles.availability_status = 'scheduled'.
        Schema::create('freelancer_availability_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday');
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->boolean('is_off')->default(true);
            $table->timestamps();

            $table->index(['freelancer_profile_id', 'weekday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_availability_slots');
    }
};
