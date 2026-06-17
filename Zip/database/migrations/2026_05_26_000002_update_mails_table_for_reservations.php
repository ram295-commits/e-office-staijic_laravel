<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->foreignId('reservation_slot_id')->nullable()->constrained('number_reservations')->nullOnDelete();
            $table->boolean('date_locked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropForeign(['reservation_slot_id']);
            $table->dropColumn(['reservation_slot_id', 'date_locked']);
        });
    }
};
