<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('city');
            $table->string('fcm_token')->nullable()->after('locale');
            $table->boolean('notify_push')->default(true)->after('fcm_token');
            $table->boolean('notify_database')->default(true)->after('notify_push');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->string('reference')->nullable()->unique()->after('id');
            $table->text('description')->nullable()->after('title');
            $table->foreignId('category_id')->nullable()->after('project_id')->constrained('property_categories')->nullOnDelete();
            $table->string('country')->default('Egypt')->after('city');
            $table->string('governorate')->nullable()->index()->after('country');
            $table->decimal('latitude', 10, 7)->nullable()->after('district');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('finishing_status')->nullable()->after('longitude');
            $table->json('amenities')->nullable()->after('finishing_status');

            // Reported vs asking financials (decimal money, never float in DB)
            $table->decimal('asking_price', 15, 2)->nullable()->after('market_price')->comment('Assignment asking price if different from paid_amount logic');
            $table->decimal('outstanding_fees', 15, 2)->default(0)->after('asking_price');
            $table->string('installment_frequency')->default('quarterly')->after('installment_amount');

            // Independent financial verification (separate from listing moderation)
            $table->string('financial_status')->default('unverified')->index()->after('status')->comment('unverified|verified|discrepancy');
            $table->decimal('verified_total_price', 15, 2)->nullable()->after('financial_status');
            $table->decimal('verified_paid_amount', 15, 2)->nullable()->after('verified_total_price');
            $table->text('financial_notes')->nullable()->after('verified_paid_amount');
            $table->foreignId('financial_verified_by')->nullable()->after('financial_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('financial_verified_at')->nullable()->after('financial_verified_by');
        });

        // Money columns become decimal(15,2)
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('total_price', 15, 2)->change();
            $table->decimal('paid_amount', 15, 2)->change();
            $table->decimal('remaining_amount', 15, 2)->default(0)->change();
            $table->decimal('market_price', 15, 2)->nullable()->change();
            $table->decimal('installment_amount', 15, 2)->nullable()->change();
        });
        Schema::table('deals', function (Blueprint $table) {
            $table->decimal('cash_to_seller', 15, 2)->change();
            $table->decimal('total_price', 15, 2)->change();
            $table->decimal('commission_amount', 15, 2)->default(0)->change();
        });
        Schema::table('evaluations', function (Blueprint $table) {
            $table->decimal('total_price', 15, 2)->change();
            $table->decimal('paid_amount', 15, 2)->change();
        });
        Schema::table('interests', function (Blueprint $table) {
            $table->decimal('cash_available', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('financial_verified_by');
            $table->dropColumn(['reference', 'description', 'country', 'governorate', 'latitude', 'longitude', 'finishing_status', 'amenities', 'asking_price', 'outstanding_fees', 'installment_frequency', 'financial_status', 'verified_total_price', 'verified_paid_amount', 'financial_notes', 'financial_verified_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['locale', 'fcm_token', 'notify_push', 'notify_database']);
        });
    }
};
