<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('cycle_number');
            $table->decimal('amount_fcfa', 18, 2);
            $table->decimal('amount_token', 36, 18)->nullable();
            $table->enum('status', ['pending', 'processing', 'confirmed', 'failed', 'late'])->default('pending')->index();
            $table->string('mobile_money_ref', 100)->nullable()->index();
            $table->string('mobile_money_provider', 20)->nullable();
            $table->string('blockchain_tx_hash', 66)->nullable();
            $table->timestamp('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->integer('late_days')->default(0);
            $table->timestamps();

            $table->index(['group_id', 'cycle_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
