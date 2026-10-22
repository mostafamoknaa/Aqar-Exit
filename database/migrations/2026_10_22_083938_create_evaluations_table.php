<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('developer_name')->nullable();
            $table->string('project_name')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('total_price');
            $table->unsignedBigInteger('paid_amount');
            $table->decimal('cancellation_percent', 5, 2)->default(15);
            $table->string('status')->default('new')->index()->comment('new|contacted|converted|closed');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
