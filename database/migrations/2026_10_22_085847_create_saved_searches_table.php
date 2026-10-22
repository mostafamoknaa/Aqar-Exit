<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('criteria')->comment('Same keys as GET /listings filters');
            $table->boolean('notify_on_match')->default(true);
            $table->timestamp('last_notified_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'notify_on_match']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
