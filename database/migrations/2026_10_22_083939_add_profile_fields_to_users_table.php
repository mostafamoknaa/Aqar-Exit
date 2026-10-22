<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change(); // mobile users may register with phone only
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('role')->default('user')->index()->after('phone');
            $table->string('avatar')->nullable()->after('role');
            $table->string('city')->nullable()->after('avatar');
            $table->boolean('is_active')->default(true)->after('city');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'avatar', 'city', 'is_active', 'last_login_at']);
        });
    }
};
