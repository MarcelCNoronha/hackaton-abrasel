<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_reviews', function (Blueprint $table) {
            $table->id();
            // unique -- 1 avaliacao por contratacao aceita, mesma trava que reviews.visit_id.
            $table->foreignId('hire_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            // feedback_to_freelancer: so' o proprio freelancer ve (autoavaliacao construtiva).
            // feedback_to_owners: so' outros donos veem, tipo referencia profissional -- os
            // dois nunca aparecem juntos pro mesmo publico, ver Owner\FreelancerController /
            // Freelancer\HireController.
            $table->text('feedback_to_freelancer')->nullable();
            $table->text('feedback_to_owners')->nullable();
            $table->timestamps();

            $table->index('freelancer_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_reviews');
    }
};
