<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_otps', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('purpose')->default('login')->index();
            $table->string('code_hash'); // never store OTPs in plaintext
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['phone', 'purpose', 'expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('phone_verified_at'));
        Schema::dropIfExists('phone_otps');
    }
};
