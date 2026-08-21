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
        Schema::table('coupon_campaigns', function (Blueprint $table) {
            // Null (both) = created directly by the owner, already active. Set proposed_by
            // only = an admin suggestion awaiting the owner's accept/reject. Both set = an
            // admin suggestion the owner has accepted (see CouponCampaign::isPending()).
            $table->foreignId('proposed_by')->nullable()->after('restaurant_id')->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupon_campaigns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proposed_by');
            $table->dropColumn('accepted_at');
        });
    }
};
