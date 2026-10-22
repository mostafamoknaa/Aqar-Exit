<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_stage')->nullable();
            $table->string('to_stage');
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['deal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_events');
    }
};
