<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->integer('sequence_number')->nullable()->after('reference_number');
            $table->string('sender_unit')->nullable()->after('sequence_number');
        });
    }

    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropColumn(['sequence_number', 'sender_unit']);
        });
    }
};
