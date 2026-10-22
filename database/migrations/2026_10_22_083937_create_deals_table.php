<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('case_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('interest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage')->default('new')->index();
            $table->unsignedBigInteger('cash_to_seller')->comment('Equals listing paid_amount at deal creation');
            $table->unsignedBigInteger('total_price');
            $table->decimal('commission_rate', 6, 4)->default(0.0125)->comment('Buyer commission, charged on completion');
            $table->unsignedBigInteger('commission_amount')->default(0);
            $table->text('notes')->nullable();
            $table->date('expected_signing_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['stage', 'case_manager_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
