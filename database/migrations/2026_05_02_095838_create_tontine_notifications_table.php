<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tontine_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['payment_due', 'payment_confirmed', 'payout_released', 'late_warning', 'member_joined', 'cycle_started', 'otp']);
            $table->text('message');
            $table->jsonb('metadata')->nullable();
            $table->enum('channel', ['sms', 'push', 'in_app'])->default('sms');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tontine_notifications');
    }
};
