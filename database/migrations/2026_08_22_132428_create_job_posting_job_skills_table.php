<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posting_job_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['job_posting_id', 'job_skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posting_job_skills');
    }
};
