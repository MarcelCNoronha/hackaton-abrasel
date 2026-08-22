<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_profile_job_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('freelancer_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['freelancer_profile_id', 'job_skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_profile_job_skills');
    }
};
