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
        Schema::create('mail_content_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_id')->constrained()->onDelete('cascade');
            $table->foreignId('changed_by')->constrained('users');
            $table->string('action'); // 'create', 'update', etc.
            $table->json('changes'); // Stores array of updated fields -> new values
            $table->json('old_values'); // Stores array of original fields -> old values
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_content_logs');
    }
};
