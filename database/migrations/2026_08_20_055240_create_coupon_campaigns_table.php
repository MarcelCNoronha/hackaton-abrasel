<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupon_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            // texto livre do beneficio (ex.: "10% de desconto"), sem qualquer condicionamento a nota da review.
            $table->string('benefit_description');

            $table->date('starts_at');
            $table->date('ends_at');
            $table->unsignedInteger('coupon_validity_days');

            $table->unsignedInteger('quantity_available')->nullable();
            $table->unsignedInteger('per_user_limit')->default(1);
            $table->decimal('min_consumption', 10, 2)->nullable();

            // dias da semana permitidos para uso do cupom emitido; null = todos os dias.
            $table->json('allowed_weekdays')->nullable();
            $table->time('allowed_hours_start')->nullable();
            $table->time('allowed_hours_end')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_campaigns');
    }
};
