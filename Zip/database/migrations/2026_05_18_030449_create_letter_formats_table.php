<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_formats', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // incoming, outgoing, internal
            $table->string('format_string');
            $table->timestamps();
        });
        
        // Seed default formats
        \Illuminate\Support\Facades\DB::table('letter_formats')->insert([
            ['type' => 'incoming', 'format_string' => '[NO_URUT]/SM/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'outgoing', 'format_string' => '[NO_URUT]/SK/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]', 'created_at' => now(), 'updated_at' => now()],
            ['type' => 'internal', 'format_string' => '[NO_URUT]/SI/[KODE_UNIT]/STAI-JIC/[BULAN_ROMAWI]/[TAHUN]', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_formats');
    }
};
