<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buyer_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('cities')->nullable();
            $table->json('unit_types')->nullable();
            $table->unsignedBigInteger('max_cash')->nullable()->comment('Max cash to pay seller');
            $table->unsignedBigInteger('max_installment')->nullable()->comment('Max installment amount');
            $table->unsignedTinyInteger('min_bedrooms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buyer_preferences');
    }
};
