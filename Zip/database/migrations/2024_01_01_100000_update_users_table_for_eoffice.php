<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip', 30)->nullable()->after('name');
            $table->string('department', 100)->nullable()->after('nip');
            $table->string('position', 100)->nullable()->after('department');
            $table->enum('role', ['admin', 'manager', 'staff'])->default('staff')->after('position');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('avatar')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nip', 'department', 'position', 'role', 'is_active', 'avatar', 'last_login_at']);
        });
    }
};
