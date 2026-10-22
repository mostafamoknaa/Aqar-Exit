<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('cash_available')->nullable()->comment('Cash buyer can pay seller now');
            $table->string('status')->default('new')->index()->comment('new|contacted|qualified|converted|rejected');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->unique(['listing_id', 'buyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interests');
    }
};
