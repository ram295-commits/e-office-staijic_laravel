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
            // Rename mail_date to tanggal_surat
            $table->renameColumn('mail_date', 'tanggal_surat');
            
            // Add unit_id, is_backdated, and jenjang
            $table->foreignId('unit_id')->nullable()->after('document_type_id')->constrained('units')->nullOnDelete();
            $table->boolean('is_backdated')->default(false)->after('status');
            $table->string('jenjang')->nullable()->after('sender_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mails', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'is_backdated', 'jenjang']);
            $table->renameColumn('tanggal_surat', 'mail_date');
        });
    }
};
