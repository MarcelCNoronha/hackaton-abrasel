<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('freelancer_profile_id')->constrained()->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            // Setado quando o dono aceita a candidatura -- vira uma HireRequest ja aceita, pra
            // reaproveitar 100% do fluxo de avaliacao que ja existe pra contratacao direta
            // (ver Owner\JobApplicationController::accept).
            $table->foreignId('hire_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['job_posting_id', 'freelancer_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
