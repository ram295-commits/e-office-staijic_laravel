<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mails', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->enum('type', ['incoming', 'outgoing', 'internal'])->default('incoming');
            $table->string('subject', 255);
            $table->text('body');
            $table->string('sender_name', 150);
            $table->string('sender_organization', 150)->nullable();
            $table->string('sender_email', 150)->nullable();
            $table->string('recipient_name', 150);
            $table->string('recipient_department', 150)->nullable();
            $table->string('recipient_email', 150)->nullable();
            $table->date('mail_date');
            $table->date('received_date')->nullable();
            $table->enum('priority', ['normal', 'urgent', 'very_urgent'])->default('normal');
            $table->enum('classification', ['open', 'confidential', 'secret'])->default('open');
            $table->enum('status', ['draft', 'pending', 'in_progress', 'completed', 'archived'])->default('pending');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index(['mail_date']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mails');
    }
};
