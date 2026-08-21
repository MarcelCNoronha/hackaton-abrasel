<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();
            $table->string('address_state', 2)->nullable();
            $table->string('address_zip_code')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->json('social_links')->nullable();

            // $ / $$ / $$$ / $$$$
            $table->string('price_range')->nullable();
            $table->string('cover_photo_path')->nullable();

            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('verified_reviews_count')->default(0);
            // {"1": 0, "2": 0, "3": 0, "4": 0, "5": 0}
            $table->json('rating_distribution')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('claimed_at')->nullable();

            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
