<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('developer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->string('unit_type')->index();
            $table->string('city')->index();
            $table->string('district')->nullable()->index();

            // Private: never returned by the public mobile API
            $table->string('unit_number')->nullable();
            $table->text('exact_address')->nullable();

            $table->unsignedSmallInteger('floor')->nullable();
            $table->decimal('area_sqm', 8, 2);
            $table->unsignedTinyInteger('bedrooms')->default(0);
            $table->unsignedTinyInteger('bathrooms')->default(0);

            // Money in EGP (whole pounds). Core Aqar Exit rule: overprice = 0,
            // buyer pays seller exactly `paid_amount`, then continues installments.
            $table->unsignedBigInteger('total_price')->comment('Old contract total price');
            $table->unsignedBigInteger('paid_amount')->comment('Cash buyer pays seller on assignment');
            $table->unsignedBigInteger('remaining_amount')->default(0);
            $table->unsignedBigInteger('market_price')->nullable()->comment('Estimated current market price');
            $table->unsignedBigInteger('installment_amount')->nullable();
            $table->unsignedSmallInteger('installments_remaining')->nullable();
            $table->date('contract_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->date('next_installment_date')->nullable();

            $table->boolean('is_silent')->default(false)->comment('Silent exit: no public listing identity');
            $table->boolean('is_featured')->default(false)->index();
            $table->string('status')->default('draft')->index();
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'city', 'unit_type']);
            $table->index(['status', 'paid_amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
