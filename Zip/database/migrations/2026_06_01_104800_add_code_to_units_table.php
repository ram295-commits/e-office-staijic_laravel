<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add column allowing NULL temporarily
        Schema::table('units', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
        });

        // 2. Populate existing data based on old getCodeAttribute logic
        $units = DB::table('units')->get();
        foreach ($units as $unit) {
            $code = match ($unit->slug) {
                'lembaga' => 'LEMBAGA',
                'bidang-1-akademik' => 'AKD',
                'bidang-2-adm-umum-keuangan' => 'KU',
                'bidang-3-kemahasiswaan' => 'MHS',
                'prodi-pendidikan-hukum' => 'PRODI',
                'internal-arsip' => 'ARSIP',
                default => strtoupper(str_replace('-', '_', $unit->slug)),
            };
            
            DB::table('units')->where('id', $unit->id)->update(['code' => $code]);
        }

        // 3. Make the column NOT NULL and UNIQUE
        Schema::table('units', function (Blueprint $table) {
            $table->string('code')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
