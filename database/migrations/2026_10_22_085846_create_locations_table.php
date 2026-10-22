<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->cascadeOnDelete();
            $table->string('type')->index()->comment('country|governorate|city|district');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('slug')->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->index(['parent_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
