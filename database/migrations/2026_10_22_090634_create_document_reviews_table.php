<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index()->comment('submitted|verified|rejected|replaced');
            $table->text('notes')->nullable();
            $table->json('snapshot')->nullable()->comment('type/original_name/status at time of review');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_reviews');
    }
};
